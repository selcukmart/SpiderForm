<?php

namespace FormGenerator\Render\RenderEngines;

/**
 * @author selcukmart
 * 2.02.2022
 * 11:41
 */
class TwigExample extends AbstractRenderEngines implements RenderInterface
{
    /**
     * @desc This code does not run, it is an extendable example.
     */
    public function createHtmlOutput(string $template): string
    {
        // This is your twig,blade,mustache etc object
        $renderObject = $this->formGenerator->getRenderobject();
        // this is place of the templte
        $template = $this->formGenerator->getBuildFormat() . '/' . $template . '.html.twig';
        $template_full_path = $renderObject->getTemplateDir() . $template;
        // If not exists the system will use its own dom output.
        if (is_file($template_full_path)) {
            // this is the variables
            $this->input_parts = defaults_form_generator($this->render->getInputParts(), $this->render->getInputVariables());
            // you must assign here
            foreach ($this->input_parts as $index => $input_part) {
                $renderObject->assign($index, $input_part);
            }
            // this is the success result
            $this->setResult(true);

            // this is your object(twig/blade etc) render method
            return $renderObject->render($template);
        }
        $this->setErrorMessage('There is no tpl file for this template ' . $template);
        return '';

    }

}