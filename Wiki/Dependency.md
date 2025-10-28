###### What Does This Class Do?

When selected, it allows the elements or values of the selected option to be displayed on the screen.


###### How to use

###### Dependency:
The 2 options mentioned here determine what the dependent elements will be. There are 2 values (file and content)
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
                        'post_id' => 'Text Field ID',
                    ]
                ]
            ]
        ],
``` 

![Dependency1](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619097919-ok-image.png)

_______________________________________________________________________________________________________________________


###### Dependend : 
The dependend array specified here depends on the value of the upper element whose dependency is true. As seen in the code below
component_type-file (depending on file value). If the file value is selected, the following elements will be active.

  ```      
         [
            'type' => 'radio',
            'label' => 'Component Install Location',
            'dependend' => [
                'group' => 'component_type',
                'dependend' => 'component_type-file'
            ],
            'attributes' => [
                'name' => 'type5'
            ],
            'options' => [
                'data' => [
                    'from' => 'key_label_array',
                    'key_label_array' => [
                        '0' => 'Put module between Header and Footer',
                        '1' => 'Install the module alone',
                    ]
                ]
            ]
        ],
        
```
![Dependency1](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619098045-ok-image.png)

Set the dependency of the initially selected value to true. Then dependent on it based on the name value of that value
A dependend array is added to the elements, and depending on what value it is, as seen in the code above
The value of the selected value is specified.

###### In Which Situations Is It Used

As seen in the picture, when the Module is selected; Put after Header and Footer and Let the module load alone options are available.
When the text field Id is selected, the input field appears at the bottom depending on it.
