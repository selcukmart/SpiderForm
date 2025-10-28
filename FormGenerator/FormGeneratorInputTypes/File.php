<?php
/**
 * @author selcukmart
 * 25.01.2021
 * 20:26
 */

namespace FormGenerator\FormGeneratorInputTypes;


use FormGenerator\Tools\Label;

class File extends AbstractInputTypes implements InputTypeInterface
{

    public function createInput(array $item):array
    {
        $this->item = $item;
        $this->row_table = $this->formGeneratorDirector->getRow();
        $previous_file = '';
        $name = $this->item['attributes']['name'];
        if (!isset($this->item['attributes']['id'])) {
            $this->item['attributes']['id'] = $name;
        }

        $this->setLabel();

        if (isset($this->row_table[$name])) {
            $previous_file = $this->row_table[$name];
        }

        $this->item['attributes']['previous_file'] = $previous_file;

        return [
            'input' => $this->toHtml($this->item),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

    

    
}