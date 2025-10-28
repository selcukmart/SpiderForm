<?php
/**
 * @author selcukmart
 * 2.02.2022
 * 17:29
 */

use Examples\DBExamples\Libraries\Database\DB;
use FormGenerator\FormGeneratorDirector;

include __DIR__ . '/../Examples/DBExamples/config.php';
include __DIR__ . '/../Examples/DBExamples/Libraries/Database/DB.php';
if (!isset($format)) {
    die('Please don\'t execute this page.<a href="./">Bye</a>');
}
$smarty = new Smarty();
$smarty->setTemplateDir(__DIR__ . '/../SMARTY_TPL_FILES');
$smarty->setCompileDir(__DIR__ . '/template_compile');
$smarty->setCacheDir(__DIR__ . '/template_cache');
$row = [
    'id' => '7',
    'type' => '1',
    'user_id' => '8015',
    'address_identification' => 'Work Adress',
    'name' => 'Joe',
    'surname' => 'DOE',
    'address' => 'Test strasse berlin',
    'postal_code' => '28100',
    'country' => '',
    'province' => '0',
    'county' => '0',
    'district' => '0',
    'neighbourhood' => '0',
    'phone' => '',
    'mobile_phone' => '5542856789',
    'mail' => null,
    'invoice_type' => '1',
    'identification_number' => '3514950',
    'nationality_tc_or_not' => '1',
    'company_name' => '',
    'tax_department' => '',
    'tax_number' => '',
    'is_e_invoice_user' => '2',
];
$form_generator_array = [
    'data' => [
        'from' => 'row',
        'row' => $row,
        //'query' => DB::query("SELECT * FROM address WHERE id='7'"),
        //'sql' =>"SELECT * FROM address WHERE id='7'",
        'connection' => [
            /**
             * optional
             * if you will use database operation you must set this
             */
            'db' => [
                /**
                 * This must be an object, and it must implement FormGenerator\Tools\DB\DBInterface
                 * There is an example in FormGenerator\Tools\DB\ folder as DBExample
                 */
                'object' => DB::class
            ]
        ],
        /**
         * Data Structure Start For DB usage
         * There are several other data getting formats, they are explaining with other data title
         * if data comes from table id must set here
         */
        'id' => '7',
        /**
         * if it doesn't set, the system will use id column name
         */
        'id_column_name' => 'id',
        /**
         * if data comes from table it must set here
         */
        'table' => 'address',
        /// Data Structure Finish
    ],
    'build' => [
        /**
         * Optional
         * Default runs GenericBuilder
         */
        //'format' => 'Bootstrapv3FormWizard',
        'format' => $format,
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
        /**
         * optional
         */
        'input-types' => [
            // default: FormGenerator_namespace\FormGeneratorInputTypes
            // if you set your namespace the system will run your FormGeneratorInputTypes folder
            // only name space your folder name must be FormGeneratorInputTypes
            'namespace' => ''
        ],
        /**
         * optional
         */
        'build-object' => [
            // default: FormGenerator_namespace\FormGeneratorBuilder
            // if you set your namespace the system will run your FormGeneratorBuilder folder
            // only name space your folder name must be FormGeneratorBuilder
            'namespace' => ''
        ],
    ],

    /**
     * Optional
     */
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
    'input' => [
        /**
         * Optional
         * TPL file which will be into injected as  {$input},
         * {$label},{$label_desc},{$label_attributes}
         * {$form_group_class},{$input_capsule_attributes}
         * {$input_above_desc},{$input_belove_desc}
         * Default capsule_template: INPUT_CAPSULE
         * this is a TPL filename in your view folder
         */
        'capsule_template' => 'INPUT_CAPSULE',
    ],
    /**
     * Optional
     * Form Inputs
     */
    'inputs' => [
        'decision' => [
            [
                'type' => 'form_section',
                'help_block' => 'test help block',
                'label' => 'Address Information'
            ],
            // this is a form input row
            [
                'type' => 'text',
                /**
                 * Optional
                 * this is tpl filename
                 * if this is not set, the system try to use default TEXT tpl
                 * if default TEXT tpl is not set, the system try to prepare DOM
                 */
                'template' => 'TEXT_X',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'address_identification',
                ]
            ],
            [
                'type' => 'text',
                'attributes' => [
                    'name' => 'name',
                ]
            ],
            [
                'type' => 'output',
                'output' => '<div class="abc">Any Data, {$name} {$username}</div>',
                'label' => 'Any Data'
            ],
            [
                'type' => 'text',
                'attributes' => [
                    'name' => 'surname',
                ]
            ],
            [
                'type' => 'textarea',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'address',
                ]
            ],
            [
                'type' => 'text',
                'attributes' => [
                    'name' => 'postal_code',
                ]
            ],
            [
                'type' => 'text',
                'attributes' => [
                    'name' => 'phone',
                ]
            ],
            [
                'type' => 'text',
                'attributes' => [
                    'name' => 'mobile_phone',
                ]
            ],
            [
                'type' => 'text',
                'attributes' => [
                    'name' => 'mail',
                ]
            ],
        ],
        // this is other section
        'corporate-info' => [
            [
                'type' => 'form_section',
                'label' => 'Corporate Information'
            ],
            [
                'type' => 'checkbox',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'iso'
                ],
                'dependency' => 'true',
                'label' => 'Nationalities',
                'options' => [
                    'data' => [
                        'from' => 'key_label_array',
                        'key_label_array' => [
                            'us' => 'USA',
                            'gb' => 'United Kingdom',
                            'de' => 'Germany'
                        ],
//                        'from' => 'rows',
//                        'rows' => [
//                            [
//                                'iso' => 'gb',
//                                'name' => 'UK'
//                            ],
//                            [
//                                'iso' => 'us',
//                                'name' => 'USA'
//                            ],
//                            [
//                                'iso' => 'de',
//                                'name' => 'Germany'
//                            ]
//                        ],
//                        'from' => 'query',
//                        'query' => DB::query("select * from countries"),
//                        'from' => 'sql',
//                        'sql' => "select * from countries",
                        /**
                         * if using SQL/Query/ROWS, this is a MUST,key_label_array: DONT USE
                         */
//                        'settings' => [
//                            'key' => 'iso',
//                            'label' => 'name',
//                        ],
                    ],
                    'control' => [
                        'from' => 'sql',
                        'sql' => "select iso from address_countries",
                        /*
                         * after parameters render as sql, generated sql will add the sql so how the query
                         *  will go on, using WHERE or AND, if not choose the system will look at WHERE in it
                        */
                        'has_where' => false,
                        'parameters' => [
                            // optional, if is not defined the system detect as this.attributes.name: iso
                            'this_field' => 'iso',
                            // must set
                            'foreign_field' => 'address_id',
                        ]
                    ]
                    //checked values
//                    'control' => [
//                        'from' => 'key_label_array',
//                        'key_label_array' => [
//                            'gb', 'us'
//                        ]
//                    ]
                ]
            ],
            [
                'type' => 'select',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'countries'
                ],
                'dependency' => 'true',
                'options' => [
                    'data' => [
//                        'from' => 'query',
                        //'query' => DB::query("select * from countries"),
                        'sql' => "select * from countries",
                        //'from' => 'rows',
//                        'rows' => [
//                            [
//                                'iso' => 'gb',
//                                'name' => 'UK'
//                            ],
//                            [
//                                'iso' => 'us',
//                                'name' => 'USA'
//                            ],
//                            [
//                                'iso' => 'de',
//                                'name' => 'Germany'
//                            ]
//                        ],
                        'settings' => [
                            'key' => 'iso',
                            'label' => 'name',
                        ],
//                        'from' => 'key_label_array',
//                        'key_label_array' => [
//                            '1' => 'Individual',
//                            '2' => 'Institutional'
//                        ]
                    ]
                ]
            ],
            [
                'type' => 'radio',
                'attributes' => [
                    'name' => 'invoice_type'
                ],
                'help_block' => 'test help block',
                'default_value' => '1',
                'dependency' => 'true',
                'options' => [
                    'data' => [
//                        'from' => 'query',
                        //'query' => DB::query("select * from countries"),
                        //'from' => 'rows',
                        'rows' => [
                            [
                                'iso' => 'gb',
                                'name' => 'UK'
                            ],
                            [
                                'iso' => 'us',
                                'name' => 'USA'
                            ],
                            [
                                'iso' => 'de',
                                'name' => 'Germany'
                            ]
                        ],
                        'settings' => [
                            'key' => 'iso',
                            'label' => 'name',
                        ],
//                        'from' => 'key_label_array',
//                        'key_label_array' => [
//                            '1' => 'Individual',
//                            '2' => 'Institutional'
//                        ]
                    ]
                ]
            ],
            [
                'type' => 'text',
                'dependend' => [
                    'group' => 'invoice_type',
                    'dependend' => 'invoice_type-2'
                ],
                'attributes' => [
                    'name' => 'company_name',
                ]
            ],
            [
                'type' => 'text',
                'dependend' => [
                    'group' => 'invoice_type',
                    'dependend' => 'invoice_type-2'
                ],
                'attributes' => [
                    'name' => 'tax_administration',
                ]
            ],
            [
                'type' => 'text',
                'dependend' => [
                    'group' => 'invoice_type',
                    'dependend' => 'invoice_type-2'
                ],
                'attributes' => [
                    'name' => 'tax_number',
                ]
            ]
        ],
        'cv' => [
            [
                'type' => 'form_section',
                'label' => 'CV'
            ],
            [
                'type' => 'file',
                'help_block' => 'test help block',
                'label' => 'Upload Your Resume',
                'attributes' => [
                    'name' => 'cv',
                    'input_attr' => 'accept="application/pdf"'
                ]
            ],
            [
                'type' => 'image',
                'help_block' => 'test help block',
                'label' => 'Upload Your Photo',
                'attributes' => [
                    'name' => 'photo',
                    'input_attr' => 'accept="image/*"'
                ]
            ],
            [
                'type' => 'timezone',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'timezone',
                ]
            ],
            [
                'type' => 'color',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'color',
                ]
            ],
            [
                'type' => 'date',
                'attributes' => [
                    'name' => 'date',
                ]
            ],
            [
                'type' => 'datetime-local',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'datetime-local',
                ]
            ],
            [
                'type' => 'hidden',
                'attributes' => [
                    'name' => 'hidden',
                ]
            ],
            [
                'type' => 'month',
                'attributes' => [
                    'name' => 'month',
                ]
            ],
            [
                'type' => 'number',
                'help_block' => 'test help block',
                'attributes' => [
                    'name' => 'number',
                ]
            ],
            [
                'type' => 'password',
                'attributes' => [
                    'name' => 'password',
                ]
            ],
            [
                'type' => 'range',
                'attributes' => [
                    'name' => 'range',
                ]
            ],
            [
                'type' => 'search',
                'attributes' => [
                    'name' => 'search',
                ]
            ],
            [
                'type' => 'tel',
                'attributes' => [
                    'name' => 'tel',
                ]
            ],
            [
                'type' => 'time',
                'attributes' => [
                    'name' => 'time',
                ]
            ],
            [
                'type' => 'url',
                'attributes' => [
                    'name' => 'url',
                ]
            ],
            [
                'type' => 'week',
                'attributes' => [
                    'name' => 'week',
                ]
            ],
            [
                'type' => 'button',
                'label' => 'Button in Inputs',
                'attributes' => [
                    'name' => 'test'
                ]
            ]
        ],
        'save' => [
            [
                'type' => 'button-group',
                'template' => 'FORM_FOOTER',
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

$form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
$form_generator->buildHtmlOutput();
echo $form_generator->getHtmlOutput();

