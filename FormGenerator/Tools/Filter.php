<?php
/**
 * @author selcukmart
 * 31.01.2021
 * 10:18
 */

namespace FormGenerator\Tools;


use FormGenerator\FormGeneratorDirector;

class Filter
{
    private $formGenerator;

    public function __construct(FormGeneratorDirector $formGenerator)
    {
        $this->formGenerator = $formGenerator;
    }

    /**
     * input-filter
     * input-group-filter
     * input-excluding-filter
     * input-excluding-group-filter
     *
     * @author selcukmart
     * 31.01.2021
     * 15:11
     */
    public function hasFilter(): void
    {
        if ($this->hasFilterInGet()) {

            if (isset($_GET['input-filter'])) {
                $this->formGenerator->setInputFilter(explode(',', $_GET['input-filter']));
            }

            if (isset($_GET['input-excluding-filter'])) {
                $this->formGenerator->setInputExcludingFilter(explode(',', $_GET['input-excluding-filter']));
            }

            if (isset($_GET['input-group-filter'])) {
                $this->formGenerator->setInputGroupFilter(explode(',', $_GET['input-group-filter']));
            }

            if (isset($_GET['input-excluding-group-filter'])) {
                $this->formGenerator->setInputGroupExcludingFilter(explode(',', $_GET['input-excluding-group-filter']));
            }
        }
    }

    public function willFiltered(array $item, $group): bool
    {

        /**
         * This determines whether the input is displayed on insertion or editing.
         * It is independent of the filtering that comes with get.
         */
        if (isset($item['scope']) && $item['scope'] !== $this->formGenerator->getScope()) {
            return true;
        }

        /**
         * If there is no filter request with get
         */
        if (!$this->formGenerator->isHasFilter()) {
            return false;
        }

        if (!is_numeric($group) && is_string($group)) {
            if (count($this->formGenerator->getInputGroupFilter()) > 0) {
                if (in_array($group, $this->formGenerator->getInputGroupFilter(), true)) {
                    return false;
                }

                return true;
            }

            if (in_array($group, $this->formGenerator->getInputGroupExcludingFilter(), true)) {
                return true;
            }
        }

        if (!empty($item['input-id'])) {
            if (count($this->formGenerator->getInputFilter()) > 0) {
                if (in_array($item['input-id'], $this->formGenerator->getInputFilter(), true)) {
                    return false;
                }

                return true;
            }

            if (in_array($item['input-id'], $this->formGenerator->getInputExcludingFilter(), true)) {
                return true;
            }
        }


        if (count($this->formGenerator->getInputGroupFilter()) > 0) {
            return !(isset($item['group']) && in_array($item['group'], $this->formGenerator->getInputGroupFilter(), true));
        }


        if ((isset($item['group']) && in_array($item['group'], $this->formGenerator->getInputGroupExcludingFilter(), true))) {
            return true;
        }

        return false;

    }

    /**
     * @return bool
     * @author selcukmart
     * 3.02.2022
     * 13:56
     */
    private function hasFilterInGet(): bool
    {
        $has_filter = isset($_GET['input-filter']) || isset($_GET['input-group-filter']) || isset($_GET['input-excluding-filter']) || isset($_GET['input-excluding-group-filter']);
        $this->formGenerator->setHasFilter($has_filter);
        return $has_filter;
    }

    public function __destruct()
    {

    }
}