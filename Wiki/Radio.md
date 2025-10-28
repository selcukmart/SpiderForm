![file](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1618900647-ok-image.png)

###### What Does This Class Do?

Radio buttons should be used when you want your user to choose between two or more options.
They look a lot like checkboxes, but instead of allowing zero or few selections within a bunch of options, a radio button forces you to select only one.
In its simplest form, a radio button is an input element with its type property set to radio , like this:


###### How to use

```
[
            'type' => 'radio',
            'label' => 'Component Type',
            'dependency' => true,
            'attributes' => [
                'name' => 'component_type'
            ],
            'options' => [
                'data' => [
                    'from' => 'key_label_array',
                    'key_label_array' => [
                        'module' => 'Module',
                        'post_id' => 'Post ID',
                    ]
                ]
            ]
        ],
```

As with all input elements, you must define a name in the attributes field for it to be available
– without a name, the item cannot be identified when the form is sent back to a server for processing.
You also want to set a value
– this will be the value sent to the server if the radio button was selected


###### In Which Situations Is It Used
The file is used in fields that require us to make only 1 selection.





