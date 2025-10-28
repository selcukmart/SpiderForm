<?php

namespace SpiderForm\Render\RenderEngines;

use SpiderForm\SpiderFormDirector;
use SpiderForm\Render\Render;
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

    public function __construct(SpiderFormDirector $formGenerator, Render $templateObject)
    {
        $this->formGenerator = $formGenerator;
        $this->render = $templateObject;
    }

    public static function getInstance(SpiderFormDirector $formGenerator, $templateObject): AbstractRenderEngines
    {
        $class = static::class;
        if (!isset(self::$instances[SpiderFormDirector::getInstanceCount()][$class])) {
            self::$instances[SpiderFormDirector::getInstanceCount()][$class] = new static($formGenerator, $templateObject);
        }
        return self::$instances[SpiderFormDirector::getInstanceCount()][$class];
    }
}