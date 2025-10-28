<?php
/**
 * @author selcukmart
 * 11.02.2022
 * 15:04
 */

namespace Tests;

class SmartyForTests
{
    private static $smarty;

    public static function getInstance(): \Smarty
    {
        if (is_null(self::$smarty)) {
            $tpl_dir = __DIR__ ;
            $smarty = new \Smarty();
            $smarty->setTemplateDir($tpl_dir . '/TEST_TPL_FILES');
            $smarty->setCompileDir($tpl_dir . '/TEST_TPL_FILES/template_compile');
            $smarty->setCacheDir($tpl_dir . '/TEST_TPL_FILES/template_cache');
            self::$smarty = $smarty;
        }
        return self::$smarty;
    }
}