<?php
/**
 * @author selcukmart
 * 2.02.2022
 * 11:18
 */

namespace SpiderForm\SpiderFormBuilder;

use SpiderForm\SpiderFormDirector;

interface BuilderInterface
{
    public function __construct(SpiderFormDirector $formGenerator);

    public function buildHtmlOutput($items = null, $parent_group = null):void;

    public function __destruct();
}