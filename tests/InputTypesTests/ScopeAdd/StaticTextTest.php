<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;

class StaticTextTest extends TestCase
{
    public function test()
    {
        $any_data = '<div class="abc">Any Data, Input to here etc</div>';
        $form_generator_array = [
            /**
             * Optional
             * Form Inputs
             */
            'inputs' => [
                'decision' => [
                    // this is a form input row
                    [
                        'type' => 'static_text',
                        'content' => $any_data,
                        'capsule_template' => 'SIMPLE',
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<div class="form-section" style="margin-top: -15px;">
    <p class="form-control-static"> <div class="abc">Any Data, Input to here etc</div> </p>
</div>';
        $this->assertSame($expected, $html);
    }
}
