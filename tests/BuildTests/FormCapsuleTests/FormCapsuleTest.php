<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\BuildTests\FormCapsuleTests;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;
use Tests\FormDataAsRow;
use Tests\SmartyForTests;

class FormCapsuleTest extends TestCase
{

    public function testFORM_CAPSULE()
    {
        $smarty = SmartyForTests::getInstance();
        $form_generator_array = [
            'form' => [
                /**
                 * Optional
                 * Default template: FORM
                 * TPL file which will be into injected as  {$input}
                 * Default capsule_template: FORM_CAPSULE
                 * this is a TPL filename in your view folder
                 */
                'template' => 'FORM',
                'capsule_template' => 'FORM_CAPSULE',
                'attributes' => [
                    'id' => 'xform',
                    'name' => 'xform',
                    'action' => '',
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ],
            'build' => [
                /**
                 * Optional
                 * Default runs GenericBuilder
                 */
                //'format' => 'Bootstrapv3FormWizard',
                'format' => 'FORM_CAPSULE_EXAMPLES',
                /**
                 * Default Smarty
                 * optional
                 */
                'render' => [
                    // twig, mustache, blade
                    'by' => 'smarty',
                    // This must be an object
                    'smarty' => $smarty,
                ],
            ],
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
                        'type' => 'text',
                        /**
                         * tpl filename
                         */
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

        $expected = '<form action="" method="post" enctype="multipart/form-data" name="xform" id="xform">
    <div class="form-body">
        <div class="form-group " id="input-nationality_tc_or_not" >
    <label style="max-height:200px; overflow:auto" class="control-label col-md-3" >Nationality is Turkey Citizen</label>
    
    <div class="col-md-9" style="max-height:600px; overflow:auto">
        
        <input name="nationality_tc_or_not" value="1" class="" placeholder="Nationality is Turkey Citizen" __is_def="1" type="text" id="nationality_tc_or_not" >
        
    </div>
</div>
    </div>
    <div class="form-actions">
        
    </div>
</form>';
        $this->assertSame($expected, $html);
    }
    public function testFORM_CAPSULE2()
    {
        $smarty = SmartyForTests::getInstance();
        $form_generator_array = [
            'form' => [
                /**
                 * Optional
                 * Default template: FORM
                 * TPL file which will be into injected as  {$input}
                 * Default capsule_template: FORM_CAPSULE
                 * this is a TPL filename in your view folder
                 */
                'template' => 'FORM',
                'capsule_template' => 'FORM_CAPSULE2',
                'attributes' => [
                    'id' => 'xform',
                    'name' => 'xform',
                    'action' => '',
                    'method' => 'post',
                    'enctype' => 'multipart/form-data'
                ]
            ],
            'build' => [
                /**
                 * Optional
                 * Default runs GenericBuilder
                 */
                //'format' => 'Bootstrapv3FormWizard',
                'format' => 'FORM_CAPSULE_EXAMPLES',
                /**
                 * Default Smarty
                 * optional
                 */
                'render' => [
                    // twig, mustache, blade
                    'by' => 'smarty',
                    // This must be an object
                    'smarty' => $smarty,
                ],
            ],
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
                        'type' => 'text',
                        /**
                         * tpl filename
                         */
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

        $expected = '<div class="container">
    <div class="col-sm-12">
            

<form action="" method="post" enctype="multipart/form-data" name="xform" id="xform">
    <div class="form-body">
        <div class="form-group " id="input-nationality_tc_or_not" >
    <label style="max-height:200px; overflow:auto" class="control-label col-md-3" >Nationality is Turkey Citizen</label>
    
    <div class="col-md-9" style="max-height:600px; overflow:auto">
        
        <input name="nationality_tc_or_not" value="1" class="" placeholder="Nationality is Turkey Citizen" __is_def="1" type="text" id="nationality_tc_or_not" >
        
    </div>
</div>
    </div>
    <div class="form-actions">
        
    </div>
</form>
    </div>
</div>';
        $this->assertSame($expected, $html);
    }

}
