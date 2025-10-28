<?php

namespace FormGenerator\Render\RenderEngines;

use FormGenerator\FormGeneratorDirector;
use FormGenerator\Render\Render;
use GlobalTraits\ErrorMessagesWithResultTrait;

/**
 * @pattern singleton and factory
 * @author selcukmart
 * 2.02.2022
 * 11:45
 */
abstract class AbstractRenderEngines
{
    use ErrorMessagesWithResultTrait;

    private static
        $instances = [];
    protected static
        $templates;
    protected
        $formGenerator,
        $render;

    public function __construct(FormGeneratorDirector $formGenerator, Render $templateObject)
    {
        $this->formGenerator = $formGenerator;
        $this->render = $templateObject;
    }

    public static function getInstance(FormGeneratorDirector $formGenerator, $templateObject): AbstractRenderEngines
    {
        $class = static::class;
        if (!isset(self::$instances[FormGeneratorDirector::getInstanceCount()][$class])) {
            self::$instances[FormGeneratorDirector::getInstanceCount()][$class] = new static($formGenerator, $templateObject);
        }
        return self::$instances[FormGeneratorDirector::getInstanceCount()][$class];
    }
}