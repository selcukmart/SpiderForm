<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:37
 */

namespace FormGenerator\FormGeneratorInputTypes;


use FormGenerator\Tools\Label;
use FormGenerator\Tools\Row;
use Helpers\Template;

class Select extends AbstractInputTypes implements InputTypeInterface
{


    private
        $default_generator_arr = [
        'default_value' => '',
        'empty_option' => true,
        'translate_option' => false,
        'attributes' => [
            'value' => '',
            'type' => 'select',
            'class' => '',
        ],
        'option_settings' => [
            'key' => 'key',
            'label' => 'label'
        ],
        'options' => '',
        'dont_set_id' => false,
        'value_callback' => ''
    ],
        $option_settings;


    public function createInput(array $item): array
    {

        $this->item = $item;

        $this->options_data = $this->item['options'];
        $this->item = defaults_form_generator($this->item, $this->default_generator_arr);
        $this->translate_option = $this->item['translate_option'];
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

        unset($this->item['attributes']['value']);
        $input_dom_array = [
            'element' => 'select',
            'attributes' => $this->item['attributes'],
            'content' => $this->optionGenerate()
        ];


        return [
            'input' => $this->toHtml($input_dom_array),
            'label' => $this->item['label'],
            'input_capsule_attributes' => '',
            'label_attributes' => ''
        ];
    }

    private function optionGenerate()
    {
        $row = new Row($this->formGeneratorDirector,$this->options_data);
        $row->setMultipleLikeRadioCheckboxSelect(true);
        $row->setRow();
        $this->option_settings = $row->getOptionsSettings() ?? $this->option_settings;
        $options_array = $row->getRow();
        $options = '';
        if ($this->item['empty_option']) {
            $options .= '<option value="">...</option>';
        }

        $key = $this->option_settings['key'];
        $this->label = $this->option_settings['label'];

        //c($options_array);
        if (!$options_array) {
            return '';
        }
        foreach ($options_array as  $option_row) {
            $attr = [
                'value' => $option_row[$key]
            ];
            if ($this->value != '' && $option_row[$key] == $this->value) {
                $attr['selected'] = 'selected';
            }

            if (isset($this->options_data['label'])) {
                $option_label = Template::smarty($option_row, $this->options_data['label']);
            } else {
                $option_label = $option_row[$this->label];
            }

            $arr = [
                'element' => 'option',
                'attributes' => $attr,
                'content' => $option_label
            ];
            $options .= $this->toHtml($arr, 'option');
        }
        return $options;
    }


}
