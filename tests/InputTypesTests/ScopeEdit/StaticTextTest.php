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

class StaticTextTest extends TestCase
{
    public function test()
    {
        $any_data = '<div class="abc">
User ID: {$user_id}<br>
Adress ID : {$address_identification}<br>
Mobile Phone: {$tel}
</div>';
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
                        'type' => 'static_text',
                        'content' => $any_data,
                        'capsule_template' => 'SIMPLE',
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<div class="form-section" style="margin-top: -15px;">
    <p class="form-control-static"> <div class="abc">
User ID: 8015<br>
Adress ID : Work Adress<br>
Mobile Phone: +905542856789
</div> </p>
</div>';
        $this->assertSame($expected, $html);
    }
}
