<?php
/**
 * @author selcukmart
 * 5.02.2022
 * 13:29
 */

namespace FormGenerator\Tools\FormDataProviders;

use Examples\DBExamples\Libraries\Database\DB;
use FormGenerator\Tools\Row;

class Sql extends AbstractFormDataProviders implements FormDataProvidersInterface
{

    public function execute(array $generator_array): array
    {
        $this->assignData($generator_array);
        if (empty($this->getSql())) {
            $this->formGenerator->setErrorMessage('SQL empty');
            return [];
        }
        return $this->getDb()::fetch($this->getDb()::query($this->getSql()));

    }

    public function execute4multiple(array $generator_array): array
    {
        $this->assignData($generator_array);
        $query = $this->getDb()::query($this->getSql());
        $rows = [];
        foreach ($query as $item) {
            $rows[] = $item;
        }
        return $rows;
    }
}