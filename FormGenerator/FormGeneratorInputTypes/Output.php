<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 14:52
 */

namespace FormGenerator\FormGeneratorInputTypes;


use Helpers\Template;

class Output extends AbstractInputTypes implements InputTypeInterface
{


    private
        $default_generator_arr = [
        'output' => '',
        'dont_set_id' => false,
    ];


    public function createInput(array $item): array
    {
        $this->item = $item;
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);
        $this->setLabel();
        $export_type = strtoupper($this->formGeneratorDirector->getBuildType());
        $result = $this->formGeneratorDirector->renderToHtml($this->item, $export_type, true);
        $this->item['output'] = $result ?: $this->item['output'];
        $row_table = $this->formGeneratorDirector->getRow();
        $this->item['output'] = !is_null($row_table) ? Template::smarty($row_table, $this->item['output']) : $this->item['output'];
        return [
            'input' => $this->item['output'],
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }


}