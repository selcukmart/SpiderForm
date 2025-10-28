<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd\OtherInputTypes;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;

class WeekTest extends TestCase
{
    public function test()
    {
        $type = 'week';
        $form_generator_array = [
            /**
             * Optional
             * Form Inputs
             */
            'inputs' => [
                'decision' => [
                    // this is a form input row
                    [
                        'type' => $type,
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE',
                        'attributes' => [
                            'name' => 'test',
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = $form_generator->getHtmlOutput();
        $expected = '<input name="test" value="" class="" placeholder="Test" __is_def="1" type="'.$type.'" id="test" >';
        $this->assertSame($expected, $html);
    }
}
