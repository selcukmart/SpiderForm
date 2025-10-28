<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 10:19
 */

namespace FormGenerator\FormGeneratorClassTraits;

trait FormGeneratorInputTrait
{

    protected
        $inputs,
        $input_types_namespace;

    public function inputID($item)
    {
        if (isset($item['attributes']['name'])) {
            return 'input-' . $item['attributes']['name'];
        }

        if (isset($item['label'])) {
            return 'input-' . form_generator_slug($item['label']);
        }
    }

    private function setInputTypesFolderNamespace(): void
    {

        if (isset($this->generator_array['input-types']['namespace']) && !empty($this->generator_array['input-types']['namespace'])) {
            $namespace = $this->generator_array['input-types']['namespace'];
        } else {
            $namespace = $this->namespace;
        }
        $this->input_types_namespace = $namespace . '\FormGeneratorInputTypes\\';
    }

    /**
     * @return mixed
     */
    public function getInputs()
    {
        return $this->inputs;
    }

    /**
     * @return string
     */
    public function getInputTypesNamespace(): string
    {
        return $this->input_types_namespace;
    }


    public function setInputs(): void
    {
        $this->inputs = $this->generator_array['inputs'];
    }
}