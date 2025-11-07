<?php
/**
 * @author selcukmart
 * 11.02.2022
 * 15:04
 */

namespace Tests;

// Support both Smarty v4 (global namespace) and v5 (namespaced)
if (class_exists('\Smarty\Smarty')) {
    class_alias('\Smarty\Smarty', 'Tests\SmartyCompatTest');
} elseif (class_exists('\Smarty')) {
    class_alias('\Smarty', 'Tests\SmartyCompatTest');
}

class SmartyForTests
{
    private static $smarty;

    public static function getInstance(): SmartyCompatTest
    {
        if (is_null(self::$smarty)) {
            $tpl_dir = __DIR__ ;
            $smarty = new SmartyCompatTest();
            $smarty->setTemplateDir($tpl_dir . '/TEST_TPL_FILES');
            $smarty->setCompileDir($tpl_dir . '/TEST_TPL_FILES/template_compile');
            $smarty->setCacheDir($tpl_dir . '/TEST_TPL_FILES/template_cache');
            self::$smarty = $smarty;
        }
        return self::$smarty;
    }
}