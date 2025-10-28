<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 09:43
 */

namespace FormGenerator\FormGeneratorClassTraits;

use Smarty;

trait FormGeneratorClassRenderTrait
{
    protected
        $render_object,
        $render_object_by = 'smarty',
        $render_class_name;

    protected function setRenderObjectDetails(): void
    {
        if ($this->hasProvidedByUser()) {
            $this->setRenderObject($this->getSmartyByUserDefined());
        } else {
            $smarty = new Smarty();
            $smarty->setTemplateDir($this->getBaseDir() . '/../SMARTY_TPL_FILES');
            $smarty->setCompileDir($this->getBaseDir() . '/../SMARTY_TPL_FILES/template_compile');
            $smarty->setCacheDir($this->getBaseDir() . '/../SMARTY_TPL_FILES/template_cache');
            $this->setRenderObject($smarty);
        }
    }

    public function renderToHtml(array $input_parts, $template, $return = false)
    {
        $renderFactory = $this->getRenderInstance();
        $renderFactory->setInputParts($input_parts);

        return $renderFactory->createHtmlOutput($template, $return,$this->getHtmlOutputType());
    }

    /**
     * @return mixed
     * @author selcukmart
     * 7.02.2022
     * 17:11
     */
    protected function getRenderInstance()
    {
        $instance_name = 'render';
        if ($this->isSetInstance($instance_name)) {
            return $this->getInstance($instance_name);
        }
        $class = $this->getRenderClassName();
        $this->setInstance($instance_name,new $class($this));
        return $this->getInstance($instance_name);
    }


    /**
     * @return string
     * @author selcukmart
     * 7.02.2022
     * 17:11
     */
    protected function getRenderClassName(): string
    {
        if (!is_null($this->render_class_name)) {
            return $this->render_class_name;
        }
        $this->render_class_name = $this->namespace . '\Render\\Render';
        return $this->render_class_name;
    }

    /**
     * @return mixed
     */
    public function getRenderobject()
    {
        return $this->render_object;
    }

    /**
     * @param mixed $smarty
     */
    public function setRenderObject(Smarty $smarty): void
    {
        $this->render_object = $smarty;
    }

    /**
     * @return mixed
     */
    public function getRenderObjectBy()
    {
        return $this->render_object_by;
    }

    /**
     * @return bool
     * @author selcukmart
     * 8.02.2022
     * 11:22
     */
    protected function hasProvidedByUser(): bool
    {

        return isset($this->generator_array['build']['render']['by']) && $this->getSmartyByUserDefined() !== null && is_string($this->generator_array['build']['render']['by']) && !empty($this->generator_array['build']['render']['by']) && is_object($this->getSmartyByUserDefined());
    }

    /**
     * @return mixed
     * @author selcukmart
     * 8.02.2022
     * 11:23
     */
    protected function getSmartyByUserDefined()
    {
        return $this->generator_array['build']['render'][$this->generator_array['build']['render']['by']] ?? null;
    }

    /**
     * @return bool
     * @author selcukmart
     * 10.02.2022
     * 09:49
     */
    protected function isSetInstance($instance_name): bool
    {
        return isset(self::$instances[self::getInstanceCount()][$instance_name]);
    }

    /**
     * @return mixed
     * @author selcukmart
     * 10.02.2022
     * 09:50
     */
    protected function getInstance($instance_name)
    {
        return self::$instances[self::getInstanceCount()][$instance_name];
    }

    protected function setInstance($instance_name,$instance): void
    {
        self::$instances[self::getInstanceCount()][$instance_name] = $instance;
    }
}