<?php

namespace Helpers;
/**
 * Copyright (c) ONLINEKURUM.COM 2018.
 */
class Classes
{
    public function __construct()
    {
    }

    public static function prepareFromString($str): string
    {
        $str = str_replace(['/', '-'], '_', $str);
        $parsed_words = explode('_', mb_strtolower($str));
        $new_str = '';
        foreach ($parsed_words as $word) {
            $new_str .= mb_ucfirst($word);
        }
        return $new_str;
    }

    public function __destruct()
    {
    }
}