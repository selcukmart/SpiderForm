<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 10:10
 */

namespace FormGenerator\FormGeneratorClassTraits;

use FormGenerator\Tools\Filter;

trait FormGeneratorClassFilterTrait
{
    protected
        $input_excluding_filter = [],
        $input_group_filter = [],
        $input_group_excluding_filter = [],
        $filter,
        $has_filter = false,
        $input_filter = [];

    private function filterTask(): void
    {
        $this->filter = new Filter($this);
        $this->filter->hasFilter();
    }

    /**
     * @return array
     */
    public function getInputGroupExcludingFilter(): array
    {
        return $this->input_group_excluding_filter;
    }

    /**
     * @return array
     */
    public function getInputGroupFilter(): array
    {
        return $this->input_group_filter;
    }


    /**
     * @return bool
     */
    public function isHasFilter(): bool
    {
        return $this->has_filter;
    }

    /**
     * @return Filter
     */
    public function getFilter(): Filter
    {
        return $this->filter;
    }

    /**
     * @return array
     */
    public function getInputFilter(): array
    {
        return $this->input_filter;
    }

    /**
     * @return array
     */
    public function getInputExcludingFilter(): array
    {
        return $this->input_excluding_filter;
    }

    /**
     * @param bool $has_filter
     */
    public function setHasFilter(bool $has_filter): void
    {
        $this->has_filter = $has_filter;
    }

    /**
     * @param array $input_excluding_filter
     */
    public function setInputExcludingFilter(array $input_excluding_filter): void
    {
        $this->input_excluding_filter = $input_excluding_filter;
    }

    /**
     * @param array $input_filter
     */
    public function setInputFilter(array $input_filter): void
    {
        $this->input_filter = $input_filter;
    }

    /**
     * @param array $input_group_excluding_filter
     */
    public function setInputGroupExcludingFilter(array $input_group_excluding_filter): void
    {
        $this->input_group_excluding_filter = $input_group_excluding_filter;
    }

    /**
     * @param array $input_group_filter
     */
    public function setInputGroupFilter(array $input_group_filter): void
    {
        $this->input_group_filter = $input_group_filter;
    }

}