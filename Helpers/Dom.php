<?php

/**
 * Copyright (c) ONLINEKURUM.COM 2018.
 */

namespace Helpers;

class Dom
{

    private static $defaults = [
        'element' => 'div',
        'attributes' => [],
        'content' => ''
    ],
        $attr_defaults = [
        'prefix' => '',
        'cache' => true
    ],
        $self_closed_elements = [
        'link',
        'meta',
        'input'
    ];

    private const ELEMENT = '<{{ELEMENT}} {{ATTRIBUTES}}>{{CONTENT}}</{{ELEMENT}}>';
    private const SELF_CLOSED_ELEMENT = '<{{ELEMENT}} {{ATTRIBUTES}}>';

    public function __construct()
    {

    }

    /**
     * @param array $elements
     * @param string $content
     * @return string
     */
    public static function htmlGenerator(array $elements, string $content = ''): string
    {
        if (isset($elements['content']) && (is_string($elements['content']) || empty($elements['content']))) {
            $elements = [$elements];
        }
        if (_sizeof_form_generator($elements) > 0) {
            foreach ($elements as $element) {
                if (isset($element['content']) && is_array($element['content']) && _sizeof_form_generator($element['content']) > 0) {
                    $element['content'] = self::htmlGenerator($element['content'], $content);
                }
                if (is_array($element)) {
                    $content .= self::core($element);
                }
            }
        }
        return $content;
    }

    /**
     * @param array $element
     * @return string
     * @access private
     */
    private static function core(array $element): string
    {
        if (isset($element['attributes']) && is_array($element['attributes'])) {
            if (_sizeof_form_generator($element['attributes']) > 0) {
                $element['attributes'] = self::makeAttr($element);
            } else {
                $element['attributes'] = '';
            }
        } else {
            $element['attributes'] = '';
        }
        return self::element($element);
    }

    /**
     * @param array $data
     * @return string
     * @access private
     */
    private static function element(array $data): string
    {
        $data = set_defaults_form_generator($data, self::$defaults);
        if (in_array($data['element'], self::$self_closed_elements)) {
            $template = self::SELF_CLOSED_ELEMENT;
        } else {
            $template = self::ELEMENT;
        }
        return Template::embed($data, $template, [
            'use' => 'd'
        ]);
    }

    public static function base64($value)
    {
        return str_replace(['=', '+'], ['XYZYXY', 'CXCXCXCXCX'], base64_encode(json_encode($value)) . '__BASE64');
    }

    /**
     * @param array $conf
     * @return string
     */
    public static function makeAttr(array $conf): string
    {
        if (isset($conf['fields'])) {
            $conf['attributes'] = $conf['fields'];
            unset($conf['fields']);
        }
        $conf = set_defaults_form_generator($conf, self::$attr_defaults);
        $has_field = self::hasField($conf);
        if ($has_field) {

            $output = "";

            if (isset($conf['attributes'])) {
                $operator = '=';
                $operator_nick = ' ';

                foreach ($conf['attributes'] as $key => $value) {
                    if (is_array($value)) {
                        $value = self::base64($value);
                    }

                    if (is_numeric($key)) {
                        $output .= $conf['prefix'] . $value . $operator . '"' . $value . '"' . $operator_nick;
                    } else {
                        $output .= $conf['prefix'] . $key . $operator . '"' . $value . '"' . $operator_nick;
                    }
                }
            }

            if (isset($conf['groups']) && is_array($conf['groups'])) {
                if (!isset($conf['groups']['cache']) && isset($conf['cache'])) {
                    $conf['groups']['cache'] = $conf['cache'];
                }

                if (!isset($conf['groups']['prefix']) && isset($conf['prefix'])) {
                    $conf['groups']['prefix'] = $conf['prefix'];
                }

                $output .= self::{__FUNCTION__}($conf['groups']);
            }

            return $output;
        }

        return '';
    }

    /**
     * @param array $conf
     * @return bool
     * @author selcukmart
     * 3.02.2022
     * 12:37
     */
    private static function hasField(array $conf): bool
    {
        return (isset($conf['groups']['attributes']) && is_array($conf['groups']['attributes'])) || (isset($conf['attributes']) && is_array($conf['attributes']));
    }

    public function __destruct()
    {

    }

}