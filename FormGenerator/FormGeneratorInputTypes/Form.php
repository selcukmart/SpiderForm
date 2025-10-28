<?php
/**
 * @author selcukmart
 * 25.01.2021
 * 20:26
 */

namespace FormGenerator\FormGeneratorInputTypes;


class Form extends AbstractInputTypes implements InputTypeInterface
{

    public function createInput(array $item): array
    {
        $this->item = $item;
        $input_dom_array = [
            'element' => 'form',
            'attributes' => $this->item['attributes'],
            'content' => ''
        ];
        $this->html_output_type = 'form';
        return [
            'input' => $this->toHtml($input_dom_array),
            'label' => '',
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

}