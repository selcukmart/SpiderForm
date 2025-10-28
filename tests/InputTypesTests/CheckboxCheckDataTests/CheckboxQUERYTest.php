<?php
/**
 * @author selcukmart
 * 9.02.2022
 * 10:49
 */

namespace Tests\InputTypesTests\CheckboxCheckDataTests;


use Examples\DBExamples\Libraries\Database\DB;
use FormGenerator\FormGeneratorDirector;
use PHPUnit\Framework\TestCase;
use Tests\FormDataAsRow;

require_once __DIR__ . '/../../../Examples/DBExamples/Config/Db.php';
class CheckboxQUERYTest extends TestCase
{
    public function testSql()
    {
        $form_generator_array = [
            'data' => [
                'row' => FormDataAsRow::getData(),
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
            ],
            /**
             * Optional
             * Form Inputs
             */
            'inputs' => [
                'decision' => [
                    [
                        // this is a form input row
                        'type' => 'checkbox',
                        'capsule_template' => 'SIMPLE',
                        'attributes' => [
                            'name' => 'iso'
                        ],
                        'label' => 'Nationalities',
                        'options' => [
                            'data' => [
                                'from' => 'query',
                                'query' => DB::query("select * from countries"),
                                'settings' => [
                                    'key' => 'iso',
                                    'label' => 'name',
                                ],
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
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<input type="checkbox" id="iso-us" name="iso[]" value="us" checked="checked"><label for="iso-us"> USA</label><br>    <input type="checkbox" id="iso-gb" name="iso[]" value="gb" checked="checked"><label for="iso-gb"> UK</label><br>    <input type="checkbox" id="iso-de" name="iso[]" value="de" ><label for="iso-de"> Germany</label><br>';
        $this->assertSame($expected, $html);
    }

    public function testKeyValueArray()
    {
        $form_generator_array = [
            'data' => [
                'row' => FormDataAsRow::getData(),
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
            ],
            /**
             * Optional
             * Form Inputs
             */
            'inputs' => [
                'decision' => [
                    [
                        // this is a form input row
                        'type' => 'checkbox',
                        'capsule_template' => 'SIMPLE',
                        'attributes' => [
                            'name' => 'iso'
                        ],
                        'label' => 'Nationalities',
                        'options' => [
                            'data' => [
                                'from' => 'query',
                                'query' => DB::query("select * from countries"),
                                'settings' => [
                                    'key' => 'iso',
                                    'label' => 'name',
                                ],
                            ],
                            'control' => [
                                'from' => 'key_label_array',
                                'key_label_array' => [
                                    'gb', 'de'
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        ];
        $form_generator = new FormGeneratorDirector($form_generator_array, 'edit');
        $form_generator->buildHtmlOutput();
        $html = trim($form_generator->getHtmlOutput());
        $expected = '<input type="checkbox" id="iso-us" name="iso[]" value="us" ><label for="iso-us"> USA</label><br>    <input type="checkbox" id="iso-gb" name="iso[]" value="gb" checked="checked"><label for="iso-gb"> UK</label><br>    <input type="checkbox" id="iso-de" name="iso[]" value="de" checked="checked"><label for="iso-de"> Germany</label><br>';
        $this->assertSame($expected, $html);
    }

}
