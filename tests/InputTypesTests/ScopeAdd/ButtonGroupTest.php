<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;

class ButtonGroupTest extends TestCase
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
                        'type' => 'button-group',
                        'capsule_template' => 'BUTTON_GROUP_CAPSULE_SIMPLE',
                        'buttons' => [
                            [
                                'type' => 'button',
                                'label' => 'Button',
                                'attributes' => [
                                    'class' => 'btn btn-success',
                                    'name' => 'btn1'
                                ]
                            ],
                            [
                                'type' => 'reset',
                                'label' => 'Reset',
                                'attributes' => [
                                    'class' => 'btn btn-success',
                                    'name' => 'btn2'
                                ]
                            ],
                            [
                                'type' => 'submit',
                                'label' => 'Submit',
                                'attributes' => [
                                    'class' => 'btn btn-success',
                                    'name' => 'btn3'
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = $form_generator->getHtmlOutput('buttons');
        $expected = '<button name="btn1" type="button" class="btn btn-success">Button</button><button name="btn2" type="reset" class="btn btn-success">Reset</button><button name="btn3" type="submit" class="btn btn-success">Submit</button>';
        $this->assertSame($expected, $html);
    }
}
