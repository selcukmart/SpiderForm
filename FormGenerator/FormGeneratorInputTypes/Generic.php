<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 14:52
 */

namespace FormGenerator\FormGeneratorInputTypes;


class Generic extends AbstractInputTypes implements InputTypeInterface
{
    private
        $default_generator_arr = [
        'default_value' => '',
        'attributes' => [
            'value' => '',
            'class' => '',
            'placeholder' => ''
        ],
        'dont_set_id' => false,
        'value_callback' => ''
    ];


    public function createInput(array $item): array
    {

        $this->item = $item;
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);
        $this->item['attributes']['type'] =  $this->item['attributes']['type']??$this->item['type'];
        $this->item['attributes']['name'] = $this->item['attributes']['name'] ?? $this->item['attributes']['type'];
        $field = $this->item['attributes']['name'];
        $this->cleanIDInAttributesIfNecessary();
        $row_table = $this->formGeneratorDirector->getRow();


        $this->valueCallback($row_table, $field);

        $this->setDefinedDefaultValue();
        $this->setDBDefaultValue($field);
        $this->setLabel();

        if (empty($this->item['attributes']['placeholder'])) {
            $this->item['attributes']['placeholder'] = $this->label->getLabelWithoutHelp();
        }

        $input_dom_array = [
            'element' => 'input',
            'attributes' => $this->item['attributes'],
            'content' => ''
        ];

        /**
         * For encapsulation div or etc...
         */
        return [
            'input' => $this->toHtml($input_dom_array),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

}