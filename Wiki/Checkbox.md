![Checkbox1](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619012948-ok-image.png)

###### What Does This Class Do?

Checkbox provides options for the user to be able to select one, several or all of them.
used. Thanks to the Checked feature, we can check whether it is selected or not.

###### How to use

```php
[
                'type' => 'checkbox',
                'attributes' => [
                    'name' => 'iso'
                ],
                'dependency' => 'true',
                'label' => 'Nationalities',
                'options' => [
                    'data' => [
                        'from' => 'key_label_array',
                        'key_label_array' => [
                            'gb' => 'UK',
                            'us' => 'USA',
                            'de' => 'Germany',
                        ]
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
//                            'a', 'c'
//                        ]
//                    ]
                ]
            ],
```

The selected values are passed to the parent_id array. In the Attributes section, the name or other properties of the
input are specified.
Two tables are used here. sql table and foreign table. The sql table querying in Options is this_field in the control
part.
The sql query in the control array matches foreign_field.

###### Data Structure

it is used using 'data' parameter under 'options'

1. SQL

```php
'data' => [
                        'from' => 'sql',
                        'sql' => "select * from countries",
                        'settings' => [
                            'key' => 'iso',
                            'label' => 'name',
                        ],
                    ],
```
2. QUERY

```php
'data' => [
                        'from' => 'query',
                        'query' => DB::query("select * from countries"),
                        'settings' => [
                            'key' => 'iso',
                            'label' => 'name',
                        ],
                    ],
```
3. ROWS

```php
'data' => [
                        'from' => 'rows',
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
                    ],
```
4. KEY LABEL ARRAY

```php
'data' => [
                        'from' => 'key_label_array',
                        'key_label_array' => [
                            'us' => 'USA',
                            'gb' => 'United Kingdom',
                            'de' => 'Germany'
                        ]
                    ],
```

###### Data Control Structure

Using these structures: It can be checked if it is checked or not.

it is used 'control' parameter under 'options'

1. SQL

```php
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
```

2. Key Label Control

```php
'control' => [
                        'from' => 'key_label_array',
                        'key_label_array' => [
                            'gb', 'us'
                        ]
                    ]
```

###### In Which Situations Is It Used

For example, when we ask about your interests, you may have one or more interests.
we use the checkbox object wherever we want.
