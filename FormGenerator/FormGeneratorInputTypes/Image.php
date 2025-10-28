<?php
/**
 * @author selcukmart
 * 25.01.2021
 * 20:26
 */

namespace FormGenerator\FormGeneratorInputTypes;



class Image extends AbstractInputTypes implements InputTypeInterface
{

    public function createInput(array $item):array
    {
        $this->item = $item;
        $this->field = $this->item['attributes']['name'];
        $this->row_table = $this->formGeneratorDirector->getRow();
        $this->setLabel();
        $this->item['attributes']['src'] = $this->row_table[$this->field] ?? '';
        $this->unit_parts = [
            'input' => $this->formGeneratorDirector->renderToHtml($this->item['attributes'],'IMAGE',true),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
        return $this->unit_parts;
    }
    
}