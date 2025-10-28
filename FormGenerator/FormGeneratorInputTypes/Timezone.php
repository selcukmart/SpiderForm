<?php
/**
 * @author selcukmart
 * 13.02.2021
 * 11:47
 */

namespace FormGenerator\FormGeneratorInputTypes;


use Helpers\Dom;
use FormGenerator\Tools\Label;


class Timezone extends AbstractInputTypes implements InputTypeInterface
{


    private
        $default_generator_arr = [
        'default_value' => '',
        'attributes' => [
            'value' => '',
            'type' => 'select',
            'class' => '',
        ],
        'dont_set_id' => false,
        'value_callback' => ''
    ];


    public function createInput(array $item): array
    {
        $this->item = $item;
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);

        $field = $this->field = $this->item['attributes']['name'];

        $this->row_table = $this->formGeneratorDirector->getRow();

        if (isset($this->row_table[$this->field])) {
            $this->item['attributes']['value'] = $this->row_table[$this->field];

        }

        $this->setDefinedDefaultValue();
        $this->setDBDefaultValue($field);
        $this->setLabel();

        $this->value = $this->item['attributes']['value'];

        $input_dom_array = [
            'element' => 'select',
            'attributes' => $this->item['attributes'],
            'content' => $this->optionGenerate()
        ];


        return [
            'input' => $this->toHtml($input_dom_array),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }


    private function optionGenerate()
    {

        $this->options_array = timezone_identifiers_list();
        $this->options = '';

        foreach ($this->options_array as $value) {
            $attr = [
                'value' => $value
            ];
            if ($this->value != '' && $value == $this->value) {
                $attr['selected'] = 'selected';
            }

            $option_label = $value;

            $arr = [
                'element' => 'option',
                'attributes' => $attr,
                'content' => $option_label
            ];
            $this->options .= Dom::htmlGenerator($arr);
        }
        return $this->options;
    }


}