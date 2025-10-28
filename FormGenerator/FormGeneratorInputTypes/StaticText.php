<?php
/**
 * @author selcukmart
 * 27.01.2021
 * 23:19
 */

namespace FormGenerator\FormGeneratorInputTypes;


use Helpers\Template;

class StaticText extends AbstractInputTypes implements InputTypeInterface
{

    public function createInput(array $item): array
    {
        $this->item = $item;
        $this->item ['template'] = 'STATIC_TEXT';
        $row_table = $this->formGeneratorDirector->getRow();
        if (isset($this->item['content_callback']) && is_callable($this->item['content_callback'])) {
            $this->item['content'] = call_user_func_array($this->item['content_callback'], [$row_table, $this->item]);
        } else {
            $this->item['content'] = is_array($row_table) ? Template::smarty($row_table, $this->item['content']) : $this->item['content'];
        }
        $this->item['attributes']['content'] = $this->item['content'];
        $this->setLabel();
        $this->unit_parts = $this->item;

        return [
            'input' => $this->toHtml($this->unit_parts, $this->item['template']),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }


}