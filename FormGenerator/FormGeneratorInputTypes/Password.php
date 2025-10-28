<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:37
 */

namespace FormGenerator\FormGeneratorInputTypes;


use FormGenerator\Tools\Label;

class Password extends AbstractInputTypes implements InputTypeInterface
{
    

    private        
        $unit_parts = [],
        $default_generator_arr = [
        'attributes' => [
            'value' => '',
            'type' => 'password',
            'class' => '',
            'placeholder' => ''
        ]
    ];

    

    public function createInput(array $item):array
    {
        $this->item = $item;
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);

        $this->cleanIDInAttributesIfNecessary();

        $this->item['attributes']['value'] = '';

        $this->setLabel();

        if (empty($this->item['attributes']['placeholder'])) {
            $this->item['attributes']['placeholder'] = $this->label->getLabelWithoutHelp();
        }

        $input_dom_array = [
            'element' => 'input',
            'attributes' => $this->item['attributes'],
            'content' => ''
        ];
        $this->unit_parts = [
            'input' => $this->toHtml($input_dom_array),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];

        return $this->unit_parts;
    }

    

    
}