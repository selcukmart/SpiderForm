<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:37
 */

namespace FormGenerator\FormGeneratorInputTypes;


use FormGenerator\Tools\CheckedControl;
use FormGenerator\Tools\Label;
use FormGenerator\Tools\Row;

class Checkbox extends AbstractInputTypes implements InputTypeInterface
{


    private
        $default_generator_arr = [
        'default_value' => '',
        'attributes' => [
        ],
        'option_settings' => [
            'key' => 'key',
            'label' => 'label',
        ],
        'options' => '',
        'dont_set_id' => false,
        'value_callback' => ''
    ],

        $row_data = [],
        $row = [],
        $units_output = '',
        $option_settings,
        $field;


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

        return [
            'input' => $this->checkboxGenerate(),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

    private function checkboxGenerate(): string
    {

        $row = new Row($this->formGeneratorDirector, $this->row_data);
        $row->setMultipleLikeRadioCheckboxSelect(true);
        $row->setRow();
        $this->row = $row->getRow();

        $this->option_settings = $row->getOptionsSettings() ?? $this->option_settings;
        $key = $this->option_settings['key'];
        $label = $this->option_settings['label'];
        $checked_control = $this->checkedControl();

        foreach ($this->row as $option_row) {

            $id = $this->field . '-' . $option_row[$key];
            $attr = [
                'type' => 'checkbox',
                'value' => $option_row[$key],
                'id' => $id,
                'name' => $this->field . '[]'
            ];
            $attr['label'] = $option_row[$label] ?? '';
            if ($checked_control) {
                $checked_control->control($option_row[$key]);
                if ($checked_control->isChecked()) {
                    $attr['checked'] = 'checked';
                }
            }

            if (isset($this->item['dependency']) && $this->item['dependency']) {
                $arr = [
                    'data-dependency' => 'true',
                    'data-dependency-group' => $this->field,
                    'data-dependency-field' => $id
                ];
                $attr = array_merge($attr, $arr);
            }

            $arr = [
                'element' => 'input',
                'attributes' => $attr,
                'content' => $option_row[$label]
            ];
            $this->units_output .= $this->toHtml($arr);
        }
        //exit;

        return $this->units_output;
    }

    /**
     * @return false|CheckedControl
     * @author selcukmart
     * 5.02.2022
     * 11:31
     */
    private function checkedControl()
    {
        return isset($this->item['options']['control']) && is_array($this->item['options']['control']) ? new CheckedControl($this->formGeneratorDirector, $this->item['options']['control'], $this->field, $this->row_table) : false;
    }


}