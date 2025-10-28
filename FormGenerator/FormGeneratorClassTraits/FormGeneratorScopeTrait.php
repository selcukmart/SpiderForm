<?php
/**
 * @author selcukmart
 * 8.02.2022
 * 10:17
 */

namespace FormGenerator\FormGeneratorClassTraits;

trait FormGeneratorScopeTrait
{
    protected
        $scope;

    /**
     * @return mixed
     */
    public function getScope()
    {
        return $this->scope;
    }

    public function isAdd()
    {
        return $this->scope !== 'edit';
    }

    public function isEdit()
    {
        return $this->scope === 'edit';
    }

    /**
     * @param mixed $scope
     */
    public function setScope($scope): void
    {
        $this->scope = $scope;
    }
}