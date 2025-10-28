<?php
/**
 * @author selcukmart
 * 5.02.2022
 * 13:29
 */

namespace FormGenerator\Tools\FormDataProviders;


class DbTable extends AbstractFormDataProviders implements FormDataProvidersInterface
{

    public function execute(array $generator_array): array
    {
        $this->assignData($generator_array);
        if (!$this->getId()) {
            $this->formGenerator->setErrorMessage('ID empty');
            return [];
        }
        return $this->getDb()::getRow($this->getIdColumnName(), $this->getId(), $this->getTable());
    }

    public function execute4multiple(array $generator_array): array
    {
        return [$this->execute($generator_array)];
    }
}