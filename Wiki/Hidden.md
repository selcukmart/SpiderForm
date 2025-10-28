###### What Does This Class Do?

It serves to prevent the input fields with fixed values from appearing in the form by determining their default values.

###### How to use

```
                [
                    'type' => 'hidden',
                    'dependency' => true,
                    'attributes' => [
                        'name' => 'type2'
                    ],
                    'default_value'=>1,
                ]
```

In the code block above, the field whose name is type2 has a value of 1, but it is not visible in the form. If it is posted, the value 1 is gone.

###### In Which Situations Is It Used

It serves to send values that are not wanted to be sent in the background.