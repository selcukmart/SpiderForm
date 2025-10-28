<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 11:38
 */

namespace SpiderForm;


use SpiderForm\FormGeneratorClassTraits\FormGeneratorClassDataPrepareTrait;
use SpiderForm\FormGeneratorClassTraits\FormGeneratorClassHtmlBuilderTrait;
use SpiderForm\FormGeneratorClassTraits\FormGeneratorClassFilterTrait;
use SpiderForm\FormGeneratorClassTraits\FormGeneratorClassRenderTrait;
use SpiderForm\FormGeneratorClassTraits\FormGeneratorInputTrait;
use SpiderForm\FormGeneratorClassTraits\FormGeneratorMessagesTrait;
use SpiderForm\FormGeneratorClassTraits\FormGeneratorScopeTrait;

class FormGeneratorDirector
{
    private static
        $instance_count = 0,
        $instances = [];

    use
        FormGeneratorClassRenderTrait,
        FormGeneratorClassDataPrepareTrait,
        FormGeneratorClassHtmlBuilderTrait,
        FormGeneratorClassFilterTrait,
        FormGeneratorScopeTrait,
        FormGeneratorInputTrait,
        FormGeneratorMessagesTrait;

    protected
        $html_output_type = 'inputs',
        $base_dir,
        $generator_array = [],
        $namespace;

    public function __construct(array $generator_array, $scope)
    {
        $this->increaseDirectorInstanceCount();
        $this->setBaseDir();
        $this->setScope($scope);
        $this->setGeneratorArray($generator_array);
        $this->setInputs();
        $this->setRenderObjectDetails();
        $this->setBuildFormat();
        $this->setBuildType();
        $this->filterTask();
        $this->setNamespace();
        $this->setInputTypesFolderNamespace();
        $this->setDB();
        $this->databaseVariables();
        $this->setRow();
    }

    public function setNamespace(): void
    {
        $this->namespace = __NAMESPACE__;
    }

    /**
     * @param array $generator_array
     */
    public function setGeneratorArray(array $generator_array): void
    {
        $this->generator_array = $generator_array;
    }

    /**
     * @return string
     */
    public function getNamespace(): string
    {
        return $this->namespace;
    }

    /**
     * @param mixed $base_dir
     */
    public function setBaseDir(): void
    {
        $this->base_dir = __DIR__;
    }

    /**
     * @return mixed
     */
    public function getBaseDir()
    {
        return $this->base_dir;
    }

    /**
     * @return array
     */
    public function getGeneratorArray(): array
    {
        return $this->generator_array;
    }

    /**
     * @param string $html_output_type
     */
    public function setHtmlOutputType(string $html_output_type): void
    {
        $this->html_output_type = $html_output_type;
    }

    /**
     * @return string
     */
    public function getHtmlOutputType(): string
    {
        return $this->html_output_type;
    }

    private function increaseDirectorInstanceCount(): void
    {
        self::$instance_count++;
    }

    /**
     * @return int
     */
    public static function getInstanceCount(): int
    {
        return self::$instance_count;
    }

    public function __destruct()
    {

    }
}