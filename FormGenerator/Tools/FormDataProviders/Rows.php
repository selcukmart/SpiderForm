<?php
/**
 * @author selcukmart
 * 5.02.2022
 * 13:29
 */

namespace FormGenerator\Tools\FormDataProviders;

class Rows extends AbstractFormDataProviders implements FormDataProvidersInterface
{

    public function execute(array $generator_array): array
    {
        $this->assignData($generator_array);
        return $this->data['rows'];

    }

    public function execute4multiple(array $generator_array): array
    {
        return $this->execute($generator_array);
    }
}