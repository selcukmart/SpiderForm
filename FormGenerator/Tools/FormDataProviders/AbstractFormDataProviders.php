<?php
/**
 * @pattern singleton and factory
 * @author selcukmart
 * 5.02.2022
 * 13:46
 */

namespace FormGenerator\Tools\FormDataProviders;

use FormGenerator\FormGeneratorDirector;

abstract class AbstractFormDataProviders
{
    private static $instances = [];

    /**
     * @var array
     * @author selcukmart
     * 7.02.2022
     * 16:38
     */
    protected
        $generator_array,
        $formGenerator,
        $data;

    public function __construct(FormGeneratorDirector $formGenerator)
    {
        $this->formGenerator = $formGenerator;

    }

    protected function assignData(array $generator_array): void
    {
        $this->generator_array = $generator_array;
        $this->data = $this->generator_array['data'];
    }

    public static function getInstance(FormGeneratorDirector $formGenerator): AbstractFormDataProviders
    {
        $class = static::class;
        if (!isset(self::$instances[FormGeneratorDirector::getInstanceCount()][$class])) {
            self::$instances[FormGeneratorDirector::getInstanceCount()][$class] = new static($formGenerator);
        }

        return self::$instances[FormGeneratorDirector::getInstanceCount()][$class];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:15
     */
    protected function getTable()
    {
        return $this->data['table'];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:15
     */
    protected function getId()
    {
        return $this->data['id'];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:16
     */
    protected function getIdColumnName()
    {
        return $this->data['id_column_name'];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 12:16
     */
    protected function getSql()
    {
        return $this->data['sql'];
    }

    /**
     * @return mixed
     * @author selcukmart
     * 5.02.2022
     * 13:30
     */
    protected function getDb()
    {
        return $this->formGenerator->getDb();
    }

    public function __destruct()
    {

    }
}