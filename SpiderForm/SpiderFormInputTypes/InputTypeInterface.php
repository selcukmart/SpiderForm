<?php
/**
 * @author selcukmart
 * 24.01.2021
 * 14:49
 */

namespace SpiderForm\SpiderFormInputTypes;


use SpiderForm\SpiderFormDirector;

interface InputTypeInterface
{
    public function __construct(SpiderFormDirector $formGenerator);

    public function createInput(array $item):array;

    public function __destruct();
}