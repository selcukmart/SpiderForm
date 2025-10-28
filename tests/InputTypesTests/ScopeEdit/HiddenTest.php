<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeEdit;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;
use Tests\FormDataAsRow;

class HiddenTest extends TestCase
{
    public function test()
    {
        $form_generator_array = [
            'data' => [
                'row' => FormDataAsRow::getData(),
            ],
            /**
             * Optional
             * Form Inputs
             */
            'inputs' => [
                'decision' => [
                    // this is a form input row
                    [
                        'type' => 'hidden',
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE',
                        'label' => 'Nationality is Turkey Citizen',
                        'attributes' => [
                            'name' => 'nationality_tc_or_not'
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<input id="nationality_tc_or_not" name="nationality_tc_or_not" value="1" type="hidden">';
        $this->assertSame($expected, $html);
    }
}
