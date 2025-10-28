<?php
/**
 * @author selcukmart
 * 5.02.2022
 * 13:29
 */

namespace FormGenerator\Tools\FormDataProviders;

class Row extends AbstractFormDataProviders implements FormDataProvidersInterface
{

    public function execute(array $generator_array): array
    {
        $this->assignData($generator_array);
        return $this->data['row'];

    }

    public function execute4multiple(array $generator_array): array
    {
        $this->assignData($generator_array);
        return [$this->data['row']];
    }
}