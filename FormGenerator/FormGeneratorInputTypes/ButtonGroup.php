<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 14:52
 */

namespace FormGenerator\FormGeneratorInputTypes;


class ButtonGroup extends AbstractInputTypes implements InputTypeInterface
{
    const DEFAULT_TEMPLATE = 'BUTTON_GROUP_CAPSULE';


    public function createInput(array $items): array
    {
        $inputs = '';
        foreach ($items['buttons'] as $item) {
            $button = Button::getInstance($this->formGeneratorDirector);
            $result = $button->createInput($item);
            $inputs .= $result['input'];
        }
        unset($items['buttons']);
        $items['attributes']['input'] = $inputs;
        $input_dom_array = [

            'attributes' => $items['attributes']
        ];

        $default_template = $items['capsule_template'] ?? self::DEFAULT_TEMPLATE;
        $this->html_output_type = 'buttons';
        /**
         * For encapsulation div or etc...
         */
        return [
            'input' => $this->toHtml($input_dom_array, $default_template),
            'label' => '',
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

}