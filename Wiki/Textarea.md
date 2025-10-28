![file](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1618904670-ok-image.png)

###### What Does This Class Do?

It enables the creation of a text input control (textarea) with a large number of lines.

###### How to use

```
            [
                'type' => 'textarea',
                'attributes' => [
                    'name' => 'content'
                ],
                'help_block' => 'Magic Words; <br> {{NAME}},{{SURNAME}},{{MAIL}},{{TEL}} 
                <br> Adds the classic field if Appended at the End of Mail Subject:
                 {{STANDART_SUBJECT}} <br> Appended in Closing: {{ STANDART_OUTPUT}}',
                'label' => 'Mail Content'
            ],
```

The name of the input, which is determined as textarea from the Type part, is determined from the attributes part.
The title is determined from the label.

###### In Which Situations Is It Used

It is used where text input with more than one line input is needed.





