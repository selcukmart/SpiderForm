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

class OutputTest extends TestCase
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
                        'type' => 'output',
                        'output' => $any_data,
                        'label' => 'Any Data',
                        'capsule_template' => 'SIMPLE',
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<div class="abc">
User ID: 8015<br>
Adress ID : Work Adress<br>
Mobile Phone: +905542856789
</div>';
        $this->assertSame($expected, $html);
    }
}
