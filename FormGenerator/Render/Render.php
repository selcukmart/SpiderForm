<?php
/**
 * @author selcukmart
 * 7.06.2021
 * 11:26
 */

namespace FormGenerator\Render;


use FormGenerator\FormGeneratorDirector;
use Helpers\Classes;
use SmartyException;

class Render
{
    protected
        $formGeneratorDirector,
        $input_parts = [],
        $input_variables = [
        'form_group_class' => '',
        'label_attributes' => '',
        'label_desc' => '',
        'input_above_desc' => '',
        'input_belove_desc' => '',
        'label' => '',
        'attributes' => '',
        'input_attr' => ''
    ];

    public function __construct(FormGeneratorDirector $formGeneratorDirector)
    {
        $this->formGeneratorDirector = $formGeneratorDirector;
    }

    /**
     * @throws SmartyException
     */
    public function createHtmlOutput($template, $return, $html_output_type)
    {

        $htmlOutput = $this->getHtmlOutput($template);
        if ($return) {
            return $htmlOutput;
        }
        $this->formGeneratorDirector->mergeOutputAsString($htmlOutput, $html_output_type);
    }

    /**
     * @param array $input_parts
     */
    public function setInputParts(array $input_parts): void
    {
        $this->input_parts = $input_parts;
    }

    /**
     * @throws SmartyException
     */
    protected function getHtmlOutput($template)
    {

        $factoryClassname = $this->getFactoryClassname();

        $render_factory = $factoryClassname::getInstance($this->formGeneratorDirector, $this);

        $output = $render_factory->createHtmlOutput($template);
        if (!$render_factory->isResult()) {
            $this->formGeneratorDirector->setErrorMessage($render_factory->getErrorMessage());
            return false;
        }
        return $output;
    }

    public function __toString()
    {
        return static::class;
    }

    /**
     * @return array
     */
    public function getInputParts(): array
    {
        return $this->input_parts;
    }

    /**
     * @return string[]
     */
    public function getInputVariables(): array
    {
        return $this->input_variables;
    }

    /**
     * @return FormGeneratorDirector
     */
    public function getFormGeneratorDirector(): FormGeneratorDirector
    {
        return $this->formGeneratorDirector;
    }

    /**
     * @return string
     * @author selcukmart
     * 8.02.2022
     * 11:08
     */
    protected function getFactoryClassname(): string
    {
        $render_class_name = Classes::prepareFromString($this->formGeneratorDirector->getRenderObjectBy());
        return __NAMESPACE__ . '\RenderEngines\\' . $render_class_name;
    }

    public function __destruct()
    {

    }
}