<?php
/**
 * @author selcukmart
 * 27.01.2021
 * 22:59
 */

namespace FormGenerator\FormGeneratorInputTypes;


use FormGenerator\Tools\Label;

class FormSection extends AbstractInputTypes implements InputTypeInterface
{


    public function createInput(array $item): array
    {
        $this->item = $item;
        $this->item['attributes'] = [];
        $this->setLabel();

        //return $this->item;
        return [
            'input' => $this->toHtml($this->item),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

}