<?php
/**
 * @author selcukmart
 * 1.02.2022
 * 16:29
 */
function form_generator_slug($string): string
{
    $find = [
        "ç",
        "Ç",
        "Ğ",
        "ğ",
        "ı",
        "İ",
        "J",
        "ö",
        "Ö",
        "ş",
        "Ş",
        "ü",
        "Ü",
        '$',
        '€',
        "'",
        "\"",
        "?",
        "!",
        "^",
        "+",
        "%",
        "&",
        "/",
        "\\",
        "{",
        "}",
        "(",
        ")",
        "[",
        "]",
        "=",
        "*",
        "_",
        ", ",
        ";",
        ":",
        ".",
        "<",
        ">",
        "|",
        "é",
        "’",
        "&#8482;",
        "“",
        "”",
        "`",
        "~",
        "#",
        "´",
        '’',
        ',',
        "…"
    ];
    $change = [
        "c",
        "c",
        "g",
        "g",
        "i",
        "I",
        "j",
        "o",
        "Ö",
        "s",
        "s",
        "u",
        "U",
        's',
        'e',
        "-",
        "-",
        "-",
        "",
        "",
        "-",
        "-",
        "",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "-",
        "",
        "",
        "-",
        "e",
        "",
        "",
        "",
        "",
        "",
        "-",
        "-",
        "",
        '-',
        '-',
        ''
    ];
    $replaced = str_replace($find, $change, $string);
    $replaced = str_replace(["  ", "----", "---"], ["-", "-", "-"], $replaced);
    $string = str_replace("--", "-", $replaced);
    $find = [
        'é',
        'è',
        'ë',
        'ê',
        'É',
        'È',
        'Ë',
        'Ê'
    ];
    $string = str_replace($find, 'e', $string);
    $find = [
        'í',
        'ì',
        'î',
        'ï',
        'I',
        'Í',
        'Ì',
        'Î',
        'Ï',
        'İ',
        "i̇"
    ];
    $string = str_replace($find, 'i', $string);
    $find = [
        'ó',
        'ö',
        'Ö',
        'ò',
        'ô',
        'Ó',
        'Ò',
        'Ô'
    ];
    $string = str_replace($find, 'o', $string);
    $find = [
        'á',
        'ä',
        'â',
        'à',
        'â',
        'Ä',
        'Â',
        'Á',
        'À',
        'Â'
    ];
    $string = str_replace($find, 'a', $string);
    $find = [
        'ú',
        'ü',
        'Ü',
        'ù',
        'û',
        'Ú',
        'Ù',
        'Û'
    ];
    $string = str_replace($find, 'u', $string);
    $find = [
        'ç',
        'Ç'
    ];
    $string = str_replace($find, 'c', $string);
    $find = [
        '?'
    ];
    $string = str_replace($find, 's', $string);
    $string = preg_replace('/\s+/', '-', $string);
    $string = mb_strtolower($string, 'UTF-8');
    // replace non letter or digits by -
    $string = preg_replace('~[^\pL\d]+~u', '-', $string);

    // transliterate
    $string = iconv('utf-8', 'us-ascii//TRANSLIT', $string);

    // remove unwanted characters
    $string = preg_replace('~[^-\w]+~', '', $string);

    // trim
    $string = trim($string, '-');

    // remove duplicate -
    $string = preg_replace('~-+~', '-', $string);

    // lowercase
    $string = mb_strtolower($string, 'UTF-8');


    $string = preg_replace('/\s+/', '-', $string);
    return trim($string, "-");
}

if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($string, $encoding = null): string
    {
        if (is_null($encoding)) {
            $encoding = 'UTF-8';
        }
        $strlen = mb_strlen($string, $encoding);
        $firstChar = mb_substr($string, 0, 1, $encoding);
        $then = mb_substr($string, 1, $strlen - 1, $encoding);
        return mb_strtoupper($firstChar, $encoding) . $then;
    }
}

function defaults_form_generator(array $conf, $defaults)
{
    return set_defaults_form_generator($conf, $defaults);
}

function set_defaults_form_generator(array $conf, $defaults)
{
    foreach ($defaults as $key => $value) {
        $is_array = is_array($value) && !empty($value);
        if ($is_array) {
            if (!isset($conf[$key])) {
                $conf[$key] = [];
            }
            $conf[$key] = set_defaults_form_generator($conf[$key], $value);
        } elseif (!isset($conf[$key])) {
            $conf[$key] = $value;
        }
    }
    $conf['__is_def'] = true;
    return $conf;
}

function _sizeof_form_generator($data): int
{
    if ((PHP_VERSION_ID > 70300) && is_countable($data)) {
        return count($data);
    }
    return is_array($data) ? count($data) : false;
}

if ((!function_exists('mb_str_replace')) &&
    (function_exists('mb_substr')) && (function_exists('mb_strlen')) && (function_exists('mb_strpos'))) {
    function mb_str_replace($search, $replace, $subject)
    {
        if (is_array($subject)) {
            $ret = [];
            foreach ($subject as $key => $val) {
                $ret[$key] = mb_str_replace($search, $replace, $val);
            }
            return $ret;
        }

        foreach ((array)$search as $key => $s) {
            if ($s == '' && $s !== 0) {
                continue;
            }
            $r = !is_array($replace) ? $replace : (array_key_exists($key, $replace) ? $replace[$key] : '');
            $pos = mb_strpos($subject, $s, 0, 'UTF-8');
            while ($pos !== false) {
                $subject = mb_substr($subject, 0, $pos, 'UTF-8') . $r . mb_substr($subject, $pos + mb_strlen($s, 'UTF-8'), 65535, 'UTF-8');
                $pos = mb_strpos($subject, $s, $pos + mb_strlen($r, 'UTF-8'), 'UTF-8');
            }
        }
        return $subject;
    }
}
if(!function_exists('c')){
    function c($v, $return = false)
    {
        if ($return) {
            $output = '<pre>';
        } else {
            echo '<pre>';
        }
        if (is_array($v) || is_object($v)) {
            if ($return) {
                $output .= print_r($v, true);
            } else {
                print_r($v);
            }
        } elseif ($return) {
            $output .= $v;
        } elseif (is_bool($v)) {
            var_dump($v);
        } else {
            echo $v;
        }
        if ($return) {
            $output .= '</pre>';
            return $output;
        }

        echo '</pre>';
    }
}
