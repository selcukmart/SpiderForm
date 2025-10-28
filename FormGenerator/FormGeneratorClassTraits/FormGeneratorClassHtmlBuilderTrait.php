<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 10:02
 */

namespace FormGenerator\FormGeneratorClassTraits;

trait FormGeneratorClassHtmlBuilderTrait
{
    protected
        $imploded_output = [],
        $html_output = [],
        $build_format,
        $build_type;

    public function buildHtmlOutput(): void
    {

        if (!is_object($this->render_object)) {
            $this->setErrorMessage('Render Object not found');
            return;
        }

        $builderClassName = $this->getFormGeneratorBuilderClassName();
        $builder = $builderClassName::getInstance($this);
        $builder->buildHtmlOutput();

        if (isset($this->generator_array['form']) && !empty($this->generator_array['form'])) {
            $this->generator_array['form']['attributes']['inputs'] = $this->getHtmlOutput('inputs');
            $this->generator_array['form']['attributes']['buttons'] = $this->getHtmlOutput('buttons');
            $this->removeOutput('inputs');
            $this->removeOutput('buttons');
            $this->generator_array['form']['type'] = 'form';
            $this->generator_array['form']['input-id'] = $this->generator_array['form']['id'] ?? '';
            $builder->createForm($this->generator_array['form']);
        }
    }

    /**
     * @return string
     * @author selcukmart
     * 3.02.2022
     * 13:38
     */
    private function getFormGeneratorBuilderClassName(): string
    {
        if ($this->hasUserDefinedFormGeneratorBuilderObjects()) {
            $namespace = $this->getUserDefinedFormGeneratorBuilderNamespace();
        } else {
            $namespace = $this->namespace;
        }

        $class = $namespace . '\FormGeneratorBuilder\\' . $this->build_format . 'Builder';
        if (!class_exists($class)) {
            $class = $namespace . '\FormGeneratorBuilder\GenericBuilder';
        }
        return $class;
    }

    /**
     * @return string
     */
    public function getHtmlOutput($type = 'form'): string
    {
        return $this->getImplodedOutput($type);
    }

    /**
     * @param string $output
     */
    public function mergeOutputAsString(string $output, $type): void
    {
        if (!isset($this->html_output[$type])) {
            $this->html_output[$type] = [];
        }
        $this->html_output[$type][] = $output;

    }

    public function removeOutput($type): void
    {
        unset($this->html_output[$type]);
    }

    /**
     * @return mixed|string
     */
    public function getBuildFormat(): string
    {
        return $this->build_format;
    }

    /**
     * @return mixed
     */
    public function getBuildType()
    {
        return $this->build_type;
    }

    public function setBuildFormat(): void
    {
        $this->build_format = $this->generator_array['build']['format'] ?? 'generic';
    }

    public function setBuildType(): void
    {
        $this->generator_array['build']['type'] = $this->generator_array['build']['type'] ?? 'html';
        $this->build_type = $this->generator_array['build']['type'];
    }

    /**
     * @return bool
     * @author selcukmart
     * 8.02.2022
     * 11:55
     */
    private function hasUserDefinedFormGeneratorBuilderObjects(): bool
    {
        return isset($this->generator_array['build-object']['namespace']) && !empty($this->generator_array['build-object']['namespace']);
    }

    /**
     * @return mixed
     * @author selcukmart
     * 8.02.2022
     * 11:56
     */
    private function getUserDefinedFormGeneratorBuilderNamespace()
    {
        return $this->generator_array['build-object']['namespace'];
    }

    /**
     * @param $type
     * @return string
     * @author selcukmart
     * 9.02.2022
     * 09:28
     */
    protected function getImplodedOutput($type): string
    {
        if (isset($this->imploded_output[$type])) {
            return $this->imploded_output[$type];
        }

        if ($type !== 'buttons' && !isset($this->html_output[$type])) {
            $type = 'inputs';
        }

        if (!isset($this->html_output[$type])) {
            return '';
        }

        $this->imploded_output[$type] = implode('', $this->html_output[$type]);
        return $this->imploded_output[$type];
    }
}