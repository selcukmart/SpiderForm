<?php
/**
 * @author selcukmart
 * 5.02.2022
 * 13:37
 */

namespace FormGenerator\Tools\FormDataProviders;

interface FormDataProvidersInterface
{
    public function execute(array $generator_array): array;

    public function execute4multiple(array $generator_array): array;
}