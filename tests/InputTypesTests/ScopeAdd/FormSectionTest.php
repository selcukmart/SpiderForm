<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;

class FormSectionTest extends TestCase
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
                        'type' => 'form_section',
                        'label' => 'Address Information',
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE'
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<h4 class="form-section" style="text-transform: capitalize;margin-bottom: 0px; margin-top: 20px;">Address Information</h4>';
        $this->assertSame($expected, $html);
    }
}
