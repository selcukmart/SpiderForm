<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\ScopeAdd;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
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
                        'type' => 'file',
                        /**
                         * tpl filename
                         */
                        'capsule_template' => 'SIMPLE',
                        'attributes' => [
                            'name' => 'address_identification',
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'add');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<div class="fileinput fileinput-new" data-provides="fileinput">
    <div class="input-group input-medium">
        <div class="form-control uneditable-input input-fixed input-medium" data-trigger="fileinput"> <i class="fa fa-file fileinput-exists"></i>&nbsp;
            <span class="fileinput-filename"> </span> </div>
        <span class="input-group-addon btn default btn-file"> <span class="fileinput-new"> File Select </span> <span class="fileinput-exists"> Change </span>
      <input name="address_identification" id="address_identification"  type="file">
      </span> <a href="javascript:;" class="input-group-addon btn red fileinput-exists" data-dismiss="fileinput"> Delete </a> </div>
</div>';
        $this->assertSame($expected, $html);
    }
}
