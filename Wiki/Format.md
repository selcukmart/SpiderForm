###### What Does This Class Do?

It is used to specify the Bootstrap release version to use.

###### How to use

##### Using the Form Wizard:

If you want the form to be advanced in a wizard way, it is set as Bootstrapv3FormWizard in the format section.
At the same time, when &export_format=form-wizard is added to the url part, the flat form becomes a wizard structure.
```
 'build' => [
        'format' => 'Bootstrapv3FormWizard',
              ],
```
![BootstrapFormWizard](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619427114-ok-image.png)


##### Normal Form usage:
If you want the form to be a normal flat form, it is set to Bootstrapv3Form from the format section.
At the same time, when &export_format=form is added to the url part, the form wizard structure turns into a normal form structure.


```
    'build' => [
            'format' => 'Bootstrapv3Form'
                ],
```

![Bootstrapv3Form](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1619427214-ok-image.png)


###### In Which Situations Is It Used
Wizard form of the same form in the first picture as seen in ScreenShots. In the second picture, the flat form shape is set.
When filling out our form, the wizard is selected when it needs to be filled according to certain groups according to the sections. all the same
When you want to be in the form, the 2nd option is selected.
Bootstrap release version is the first array used to create the form generator.
The version of the form I will create from here
must be specified. This format is specified by the class.




