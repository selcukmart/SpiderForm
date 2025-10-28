<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 14:49
 */

namespace SpiderForm\FormGeneratorInputTypes;


use SpiderForm\FormGeneratorDirector;

interface InputTypeInterface
{
    public function __construct(FormGeneratorDirector $formGenerator);

    public function createInput(array $item):array;

    public function __destruct();
}