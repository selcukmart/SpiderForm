<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd;


use SpiderForm\SpiderFormDirector;
use PHPUnit\Framework\TestCase;

class TextTest extends TestCase
{
    public function test()
    {
        $form_generator_array = [
            /**
             * Optional
             * Form Inputs
             */
            'inputs' => [
                'decision' => [
                    // this is a form input row
                    [
                        'type' => 'text',
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE',
                        'attributes' => [
                            'name' => 'address_identification',
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new SpiderFormDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = $form_generator->getHtmlOutput();
        $expected = '<input placeholder="Address Identification" id="address_identification" name="address_identification" value="" type="text" class="form-control">';
        $this->assertSame($expected, $html);
    }
}
