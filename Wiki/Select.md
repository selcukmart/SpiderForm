![select](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1618843059-ok-image.png)

###### What Does This Class Do?

Select performs the process of selecting the data according to the sql value entered while listing and allows us to list and make a selection.

###### How to use

```
 [
                'type' => 'select',
                'translate_option' => true,
                'attributes' => [
                    'name' => 'branch_id'
                ],
                'label' => 'Branch',

                'options' => [
                    'data' => [
                        'from' => 'sql',
                        'sql' => "SELECT * FROM b WHERE c='d'"
                    ]
                ]
            ]
```

Since there is from = sql in the options section, a database query is needed.
In some cases, from = key_label_array is defined instead of sql. in these cases, the array element and its counterpart are included. An example of this is below.

```
[
            'type' => 'select',
            'attributes' => [
                'name' => 'state'
            ],
            'options' => [
                'data' => [
                    'from' => 'key_label_array',
                    'key_label_array' => [
                        '0' => 'Closed',
                        '1' => 'Open',
                    ]
                ]
            ],
            'label' => 'State'
        ];
```

###### In Which Situations Is It Used

We use this feature in case of choosing from the list that we have determined in the forms.

###### Automatic Translation of Options
With the property 'translate_option' => true, the translation of the options in the select is done automatically