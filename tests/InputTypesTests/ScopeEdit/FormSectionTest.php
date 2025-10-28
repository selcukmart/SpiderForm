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

class FormSectionTest extends TestCase
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
                        'type' => 'form_section',
                        'label' => 'Nationality is Turkey Citizen',
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE'
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<h4 class="form-section" style="text-transform: capitalize;margin-bottom: 0px; margin-top: 20px;">Nationality is Turkey Citizen</h4>';
        $this->assertSame($expected, $html);
    }
}
