<?php

namespace FormGenerator\Render\RenderEngines;

use SmartyException;

/**
 * @author selcukmart
 * 2.02.2022
 * 11:41
 */
class Smarty extends AbstractRenderEngines implements RenderInterface
{

    /**
     * @throws SmartyException
     */
    public function createHtmlOutput(string $template): string
    {
        $renderObject = $this->formGenerator->getRenderobject();

        $template = $this->getTemplateFullPath($renderObject, $template);

        if ($template) {
            $renderObject->clearAllAssign();
            $input_parts = defaults_form_generator($this->render->getInputParts(), $this->render->getInputVariables());
            foreach ($input_parts as $index => $input_part) {
                $renderObject->assign($index, $input_part);
            }
            $this->setResult(true);
            try {
                $result = $renderObject->fetch($template);
            }catch (SmartyException $exception){
                $this->setErrorMessage($exception->getMessage());
                echo $exception->getMessage();
            }
            return $result;
        }

        return '';
    }

    /**
     * @param \Smarty $renderObject
     * @param string $template
     * @return string
     * @author selcukmart
     * 8.02.2022
     * 11:14
     */
    protected function getTemplateFullPath(\Smarty $renderObject, string $template): string
    {
        if (isset(self::$templates[$template])) {
            return self::$templates[$template];
        }
        $template_dir = $renderObject->getTemplateDir()[0] . $this->formGenerator->getBuildFormat();
        if (!is_dir($template_dir)) {
            $template_path = 'Generic';
        } else {
            $template_path = $this->formGenerator->getBuildFormat();
        }
        $template_with_path = $template_path . '/' . $template . '.tpl';

        $template_full_path = $renderObject->getTemplateDir()[0] . $template_with_path;

        if (!is_file($template_full_path)) {
            self::$templates[$template] = false;
            $this->setErrorMessage('There is no tpl file for this template ' . $template_with_path);
            return '';
        }
        self::$templates[$template] = $template_with_path;
        return $template_with_path;
    }

}