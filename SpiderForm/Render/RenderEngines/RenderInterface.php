<?php
namespace SpiderForm\Render\RenderEngines;
use SpiderForm\FormGeneratorDirector;
use SpiderForm\Render\Render;

/**
 * @author selcukmart
 * 2.02.2022
 * 11:46
 */
interface RenderInterface
{
    public function __construct(FormGeneratorDirector $formGenerator, Render $templateObject);

    public function createHtmlOutput(string $template):string;
}