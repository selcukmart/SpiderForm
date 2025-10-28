<?php

/**
 * Copyright (c) ONLINEKURUM.COM 2018.
 */

namespace Helpers;


class Template
{

    public const REGEX = '/\{.*?\}/';
    public const ADVANCED_REGEX = '/\{\{.*?\}\}/';

    /**
     * @param array $defaults
     */
    public static $defaults = [
        'use' => 'b',
        'clean_no_longer' => true
    ];

    public function __construct()
    {

    }


    /**
     * @param array $data
     * @param string $template
     * @param array $config
     * @return string
     */
    public static function embed(array $data, string $template, array $config = []): string
    {
        $config = set_defaults_form_generator($config, self::$defaults);
        extract($config, EXTR_OVERWRITE);
        return self::html($data, $template, $use, $config);
    }

    public static function smarty(array $data, string $template, $clean_no_longer = true): string
    {
        return self::embed($data, $template, [
            'use' => 'smarty',
            'clean_no_longer' => $clean_no_longer,
            'lowercase' => true
        ]);
    }

    public static function embed_d(array $data, $template): string
    {
        return self::embed($data, $template, ['use' => 'd']);
    }

    /**
     * Temel template yaklaşımı
     * Sadece değişken yerleşimi
     * @param array $data
     * @param string $template
     * @param string $use
     * @param array $config
     * @param bool $lowercase
     * @return string $template
     * @accesse public
     */
    public static function html(array $data, string $template, string $use = 'b', array $config = [], bool $lowercase = false): string
    {
        if (!isset($config['__is_def']) || (!$config['__is_def'])) {
            $config = set_defaults_form_generator($config, self::$defaults);
        }

        $template = self::xhtml($data, $template, '', $use, $lowercase);
        if ($config['clean_no_longer']) {
            $template = self::turn($template, $use);
        }

        return $template;
    }


    /**
     * @param array $data
     * @param string $template
     * @param string $xkey
     * @param string $use
     * @param bool $lowercase
     * @return string $template
     */
    public static function xhtml(array $data, string $template, string $xkey = '', string $use = 'b', bool $lowercase = false): string
    {
        foreach ($data as $key => $value) {
            if ($value === 'NULL') {
                continue;
            }
            if (!empty($xkey)) {
                $key = $xkey . '.' . $key;
            }
            if (!is_array($value) && !is_object($value)) {
                $method = $use . 'Replace';
                $template = self::{$method}($key, $value, $template, $lowercase);
            } else {
                $template = self::xhtml($value, $template, $key, $use);
            }
        }
        //c($template);
        return $template;
    }


    public static function smartyReplace($key, $value, $template)
    {

        if (is_string($key) && (is_string($value) || is_numeric($value)) && is_string($template)) {
            $output = mb_str_replace('{$' . $key . '}', $value, $template);
        } else {
            $output = $template;
        }

        return $output;
    }

    /**
     * @param string $template_name
     * @param array $data
     */
    public static function set(string $template_name, array $data)
    {
        if (defined('TEMPLATES_DIR')) {
            $temp = TEMPLATES_DIR . '/' . $template_name . '.php';
        } elseif (defined('ADMIN_TEMPLATE_DIR')) {
            $temp = ADMIN_TEMPLATE_DIR . '/' . $template_name . '.php';
        } elseif (defined('HTML_TEMPLATE_DIR')) {
            $temp = HTML_TEMPLATE_DIR . '/' . $template_name . '.php';
        }
        if (isset($temp) && file_exists($temp)) {
            $template = file_get_contents($temp);
            return self::html($data, $template);
        }

        return false;
    }

    /**
     * @param string $template
     * @param string $use
     * @return string $output
     */
    public static function turn(string $template, string $use = 'b'): string
    {
        if ($use === 'b') {
            $regex = self::REGEX;
        } else {
            $regex = self::ADVANCED_REGEX;
        }
        return preg_replace($regex, '', $template);
    }

    /**
     * @param array $data
     * @param $template
     * @param string $use
     * @return mixed|string|string[]
     * @author selcukmart
     */
    public static function onlyReplaceDontClear(array $data, string $template, string $use = 'b')
    {
        foreach ($data as $key => $value) {
            $template = self::replaceCore($key, $value, $template, $use);
        }
        return $template;
    }

    private static function replaceCore(string $key, $value, string $template, string $use): string
    {
        if (is_numeric($value) || is_string($value) || is_bool($value)) {
            $method = $use . 'Replace';
            $template = self::{$method}($key, $value, $template);
        } else {
            $value = (array)$value;
            foreach ($value as $index => $val) {
                $template = self::replaceCore($index, $val, $template, $use);
            }
        }
        return $template;
    }

    /**
     * @param array $data
     * @param string $template
     * @return string @sonuc
     */
    public static function h(array $data, string $template): string
    {

        foreach ($data as $key => $value) {
            if (is_array($value) && _sizeof_form_generator($value) > 0) {
                foreach ($value as $k => $v) {
                    if (!is_array($v)) {
                        $template = self::replace($key, $k, $v, $template);
                    }
                }
            } else {
                $template = self::bReplace($key, $value, $template);
            }
        }
        return self::turn($template);
    }


    public static function xExplode($val)
    {
        $arr = [
            '===', '!==', '==', '<=', '>=', '!=', '<>', '=', '<', '>',
        ];

        foreach ($arr as $value) {
            $x = explode($value, $val);
            if (isset($x[1])) {
                break;
            }
        }
        return [$x, $value];
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $template
     */
    public static function bReplace(string $key, $value, string $template): string
    {
        return str_replace('{' . mb_strtoupper($key) . '}', $value, $template);
    }

    /**
     * @param string $key
     * @param mixed @value
     * @param string $template
     */
    public static function cReplace(string $key, $value, string $template): string
    {
        if ((is_string($value) || is_numeric($value))) {
            $output = str_replace($key, $value, $template);
        } else {
            $output = $template;
        }
        return $output;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $template
     */
    public static function dReplace(string $key, $value, string $template): string
    {
        if ((is_string($value) || is_numeric($value))) {
            $output = str_replace('{{' . mb_strtoupper($key) . '}}', $value, $template);
        } else {
            $output = $template;
        }
        return $output;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param string $template
     */
    public static function eReplace(string $key, $value, string $template): string
    {
        if ((is_string($value) || is_numeric($value))) {
            $output = str_replace('[' . mb_strtoupper($key) . ']', $value, $template);
        } else {
            $output = $template;
        }
        return $output;
    }

    public static function replace(string $key, string $index, $value, string $template): string
    {
        if ((is_string($value) || is_numeric($value))) {
            $output = str_replace('{' . $key . '.' . $index . '}', $value, $template);
        } else {
            $output = $template;
        }
        return $output;
    }

    public function __destruct()
    {

    }

}