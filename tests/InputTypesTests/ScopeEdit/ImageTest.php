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

class ImageTest extends TestCase
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
                        'type' => 'image',
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
        $expected = '<div class="fileinput fileinput-new" data-provides="fileinput">
    <div class="fileinput-new thumbnail" style="width: 200px; height: 150px;"> <img src="1" alt=""> </div>
    <div class="fileinput-preview fileinput-exists thumbnail" style="max-width: 200px; max-height: 150px; line-height: 10px;"> </div>
    <div>
        <span class="btn default btn-file">
            <span class="fileinput-new"> Select Photo </span>
            <span class="fileinput-exists"> Change</span>
            <input type="hidden">
            <input name="nationality_tc_or_not" id="nationality_tc_or_not" type="file"  >
        </span>
        <a href="javascript:;" class="btn red fileinput-exists" data-dismiss="fileinput"> Delete </a> </div>
</div>
<div class="clearfix margin-top-10"><span class="label label-danger">Not!</span> IE10+, FF3.6+, Safari6.0+, Chrome6.0+ and Opera11.1+ </div>';
        $this->assertSame($expected, $html);
    }
}
