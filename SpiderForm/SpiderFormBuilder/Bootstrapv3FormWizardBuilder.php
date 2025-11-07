<?php
/**
 * @author selcukmart
 * 30.01.2021
 * 17:33
 */

namespace SpiderForm\SpiderFormBuilder;


class Bootstrapv3FormWizardBuilder extends AbstractSpiderFormBuilder implements BuilderInterface
{

    private
        $sections = [],
        $tab_contents=[],
        $is_string_group;

    public function buildHtmlOutput($inputs = null, $parent_group = null):void
    {
        if (is_null($inputs)) {
            $inputs = $this->formGeneratorDirector->getInputs();
        }

        // Prevent infinite recursion
        if ($this->recursion_depth >= self::MAX_RECURSION_DEPTH) {
            error_log('SpiderForm: Maximum recursion depth reached in FormWizard. Possible infinite loop in form structure.');
            return;
        }

        foreach ($inputs as $group => $item) {
            if (!is_null($parent_group)) {
                $item['group'] = $parent_group;
            }
            $item['input-id'] = $this->formGeneratorDirector->inputID($item);
            $will_filtered = $this->filter->willFiltered($item, $group);
            if ($will_filtered) {
                continue;
            }

            $this->is_string_group = !is_numeric($group) && is_string($group);
            if ($this->is_string_group && !isset($this->sections[$group])) {
                $group_label = '';
                if (isset($item[0]['label'])) {
                    $group_label = $item[0]['label'];
                }
                $this->sections[$group] = $group_label;
            }

            $this->sendDataForRender($item, $group);
        }
    }


    protected function sendDataForRender($input, $group):void
    {
        if ($this->is_string_group) {
            // Check if $input is an array before recursing
            if (!is_array($input)) {
                return;
            }

            unset($input['input-id']);
            $this->recursion_depth++;
            $this->buildHtmlOutput($input, $group);
            $this->recursion_depth--;
            return;
        }

        if ($input['type'] === 'form_section') {
            return;
        }

        $this->prepareInputParts($input);
        if (!isset($this->tab_contents[$input['group']])) {
            $this->tab_contents[$input['group']] = [];
        }

        $this->tab_contents[$input['group']][] = $this->formGeneratorDirector->renderToHtml($this->input_parts, 'TEMPLATE');
    }

}