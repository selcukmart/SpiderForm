![query](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619421581-ok-image.png)

###### How to set DATA for RADIO, SELECT, CHECKBOX or for your extender new objects

There are several options.

1. Rows
2. SQL
3. QUERY
4. Key Label Array


1. Rows

![rows](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619433063-ok-image.png)

```php
            [
                 'type' => 'radio',
                  'attributes' =>[
                 'name' => 'x'
                        ],
        'options' => [
            'from'=>'rows',//optional bu little faster
            'rows' => [
                [
                    'id' => 1,
                    'name' => 'a'
                ],
                [
                    'id' => 2,
                    'name' => 'b'
                ]
            ],
            'settings' => [
                            'key' => 'id',
                            'label' => 'name',
                        ],
        ]
    ],
                                    
               
```

2. SQL

```php
            [
            'type' => 'radio',
            'attributes' => [
                'name' => 'place'
            ],
            'empty_option' => false,
            'options' => [
                'data' => [
                    'from' => 'sql',// optional but little faster
                    'sql' => "SELECT * FROM c WHERE type='abc' AND state='ok'"
                ],
                'settings' => [
                            'key' => 'id',
                            'label' => 'name',
                        ],
            ],
            'label' => 'Menu Place',
        ],
                                     
               
```

3. QUERY
   ![sql](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619422315-ok-image.png)

```php
               [
            'type' => 'radio',
            'label' => 'Components Include Place',
            'dependend' => [
                'group' => 'components_type',
                'dependend' => 'components_type-file'
            ],
            'attributes' => [
                'name' => 'type5'
            ],
            'options' => [
                'data' => [
                    'from' => 'query',// optional but little faster
                    'query' => DB::query("SELECT 
                                * FROM a AS b
                                WHERE 
                                b.type='1' 
                                AND b.parent_id='1'
                                "),
                    'settings' => [
                            'key' => 'id',
                            'label' => 'name',
                        ],
                ]
            ]
        ],
                                          
```

4. Key Label Array

![keyvalue](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619421818-ok-image.png)

```php
          [
            'type' => 'radio',
            'label' => 'Components Include Place',
            'dependend' => [
                'group' => 'components_type',
                'dependend' => 'components_type-file'
            ],
            'attributes' => [
                'name' => 'type5'
            ],
            'options' => [
                'data' => [
                    'from' => 'key_label_array',// optional but little faster
                    'key_label_array' => [
                        'between' => 'Put module between Header and Footer',
                        'alone' => 'Install the module alone',
                    ]
                ]
            ]
        ],
                                                               
```