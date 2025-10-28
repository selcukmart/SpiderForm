<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:37
 */

namespace FormGenerator\FormGeneratorInputTypes;


use FormGenerator\Tools\Label;
use FormGenerator\Tools\Row;
use Helpers\Dom;

class Radio extends AbstractInputTypes implements InputTypeInterface
{


    private
        $default_generator_arr = [
        'default_value' => '',
        'option_settings' => [
            'key' => 'key',
            'label' => 'label',

        ],
        'attributes' => [
            'value' => '',
        ],
        'options' => '',
        'dont_set_id' => false,
        'value_callback' => ''
    ],

        $row_data = [],
        $row = [],
        $units_output = '',
        $option_settings,
        $value;


    public function createInput(array $item): array
    {
        $this->item = $item;
        $this->row_data = $this->item['options'];
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);
        $this->option_settings = $this->item['option_settings'];
        $field = $this->field = $this->item['attributes']['name'];

        $this->cleanIDInAttributesIfNecessary();

        $this->row_table = $this->formGeneratorDirector->getRow();

        if (isset($this->row_table[$this->field])) {
            $this->item['attributes']['value'] = $this->row_table[$this->field];

        }
        $this->setDefinedDefaultValue();
        $this->setDBDefaultValue($field);
        $this->setLabel();

        $this->value = $this->item['attributes']['value'];

        return [
            'input' => $this->radioGenerate(),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

    private function radioGenerate(): string
    {
        $row = new Row($this->formGeneratorDirector, $this->row_data);
        $row->setMultipleLikeRadioCheckboxSelect(true);
        $row->setRow();
        $this->row = $row->getRow();
        $this->option_settings = $row->getOptionsSettings() ?? $this->option_settings;
        $key = $this->option_settings['key'];
        $this->labelx = $this->option_settings['label'];

        foreach ($this->row as $option_row) {

            $id = $this->field . '-' . $option_row[$key];
            $attr = [
                'type' => 'radio',
                'value' => $option_row[$key],
                'id' => $id,
                'name' => $this->field,
                'label' => $option_row[$this->labelx]
            ];

            if ($this->value !== '' && $option_row[$key] == $this->value) {
                $attr['checked'] = 'checked';
            }

            if (isset($this->item['dependency']) && $this->item['dependency']) {
                $arr = [
                    'data-dependency' => 'true',
                    'data-dependency-group' => $this->field,
                    'data-dependency-field' => $id
                ];
                $attr['data_dependency'] = Dom::makeAttr(['attributes' => $arr]);
                $attr += $arr;
            }
            $arr = [
                'element' => 'input',
                'attributes' => $attr,
                'content' => ''
            ];

            $this->units_output .= $this->toHtml($arr);
        }

        return $this->units_output;
    }


}