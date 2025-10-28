<?php
/**
 * @author selcukmart
 * 13.02.2021
 * 16:55
 */

namespace FormGenerator\Tools;


use FormGenerator\FormGeneratorDirector;

class DefaultValue
{
    private
        $all_field_options = [],
        $field = '';

    public function __construct(FormGeneratorDirector $formGenerator, string $field)
    {
        if (is_object($formGenerator->getDb())) {
            $table = $formGenerator->getTable();
            if (!is_null($table)) {
                $this->all_field_options = $formGenerator->getDb()::getAllFieldOptions($table);
                $this->field = $field;
            }
        }
    }

    public function get()
    {
        if (isset($this->all_field_options[$this->field]) && $this->all_field_options[$this->field]['Default'] !== '') {
            return $this->all_field_options[$this->field]['Default'];
        }
        return '';
    }

    public function __destruct()
    {

    }
}