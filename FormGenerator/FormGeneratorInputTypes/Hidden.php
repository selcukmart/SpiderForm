<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:37
 */

namespace FormGenerator\FormGeneratorInputTypes;


class Hidden extends AbstractInputTypes implements InputTypeInterface
{


    private
        $default_generator_arr = [
        'default_value' => '',
        'attributes' => [
            'value' => '',
            'type' => 'hidden',
        ],
        'value_callback' => ''
    ];


    public function createInput(array $item): array
    {
        $this->item = $item;
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);
        $this->item['attributes']['type'] =  $this->item['attributes']['type']??$this->item['type'];
        $field = $this->field = $this->item['attributes']['name'];
        $this->item['attributes']['id'] = $this->item['attributes']['id'] ?? $this->item['attributes']['name'];

        $row_table = $this->formGeneratorDirector->getRow();


        $this->valueCallback($row_table, $field);

        $this->setDefinedDefaultValue();
        $this->setDBDefaultValue($field);

        $input_dom_array = [
            'element' => 'input',
            'attributes' => $this->item['attributes'],
            'content' => ''
        ];
        return [
            'input' => $this->toHtml($input_dom_array),
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

}