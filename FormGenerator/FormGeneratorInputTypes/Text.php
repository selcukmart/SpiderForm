<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:37
 */

namespace FormGenerator\FormGeneratorInputTypes;


class Text extends AbstractInputTypes implements InputTypeInterface
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

        return [
            'input' => $this->toHtml($input_dom_array),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

}