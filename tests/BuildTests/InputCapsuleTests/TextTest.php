<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\BuildTests\InputCapsuleTests;


use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;
use Tests\FormDataAsRow;
use Tests\SmartyForTests;

class TextTest extends TestCase
{
    

    public function testINPUT_CAPSULE()
    {
        $smarty = SmartyForTests::getInstance();
        $form_generator_array = [
            'build' => [
                /**
                 * Optional
                 * Default runs GenericBuilder
                 */
                //'format' => 'Bootstrapv3FormWizard',
                'format' => 'INPUT_CAPSULE_EXAMPLES',
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

        $expected = '<div class="form-group " id="input-nationality_tc_or_not" >
    <label style="max-height:200px; overflow:auto" class="control-label col-md-3" >Nationality is Turkey Citizen</label>
    
    <div class="col-md-9" style="max-height:600px; overflow:auto">
        
        <input name="nationality_tc_or_not" value="1" class="" placeholder="Nationality is Turkey Citizen" __is_def="1" type="text" id="nationality_tc_or_not" >
        
    </div>
</div>';
        $this->assertSame($expected, $html);
    }

    public function testINPUT_CAPSULE2()
    {
        $smarty = SmartyForTests::getInstance();
        $form_generator_array = [
            'build' => [
                /**
                 * Optional
                 * Default runs GenericBuilder
                 */
                //'format' => 'Bootstrapv3FormWizard',
                'format' => 'INPUT_CAPSULE_EXAMPLES',
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
                        'capsule_template' => 'INPUT_CAPSULE2',
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
        $expected = '<div class="form-group">
    <label >Nationality is Turkey Citizen</label>
    
    
    <input name="nationality_tc_or_not" value="1" class="" placeholder="Nationality is Turkey Citizen" __is_def="1" type="text" id="nationality_tc_or_not" >
    
</div>';
        $this->assertSame($expected, $html);
    }

    public function testINPUT_CAPSULE3()
    {
        $smarty = SmartyForTests::getInstance();
        $form_generator_array = [
            'build' => [
                /**
                 * Optional
                 * Default runs GenericBuilder
                 */
                //'format' => 'Bootstrapv3FormWizard',
                'format' => 'INPUT_CAPSULE_EXAMPLES',
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
                        'capsule_template' => 'INPUT_CAPSULE3',
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
        $expected = '<input name="nationality_tc_or_not" value="1" class="" placeholder="Nationality is Turkey Citizen" __is_def="1" type="text" id="nationality_tc_or_not" >';
        $this->assertSame($expected, $html);
    }
}
