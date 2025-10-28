<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;

class ButtonTest extends TestCase
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
                        'type' => 'button',
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE',
                        'label' => 'Button in Inputs',
                        'attributes' => [
                            'name' => 'test'
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = $form_generator->getHtmlOutput();
        $expected = '<button name="test" type="button" class="">Button in Inputs</button>';
        $this->assertSame($expected, $html);
    }
}
