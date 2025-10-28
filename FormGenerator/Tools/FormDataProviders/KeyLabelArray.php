<?php
/**
 * @author selcukmart
 * 5.02.2022
 * 13:29
 */

namespace FormGenerator\Tools\FormDataProviders;

use Examples\DBExamples\Libraries\Database\DB;
use FormGenerator\Tools\Row;

class KeyLabelArray extends AbstractFormDataProviders implements FormDataProvidersInterface
{

    public function execute(array $generator_array): array
    {
        $this->assignData($generator_array);
        $row = [];
        foreach ($this->data['key_label_array'] as $index => $datum) {
            $row[] = [
                'key' => $index,
                'label' => $datum
            ];
        }
        return $row;
    }

    public function execute4multiple(array $generator_array): array
    {
        return $this->execute($generator_array);
    }
}