![Formsection](https://s3.eu-central-1.amazonaws.com/static.testbank.az/uploads/files/15-1618918226-ok-image.png)

###### What Does This Class Do?

Lists items that are related to each other.

###### Nasıl Kullanılır

```
   [
        'type' => 'output',
        'output' => '<div class="locales">' . Locales::select($parent_id = 0) . '</div>',
        'label' => 'Locales'
     ]
```

Lists the linked items in the form of a select box.

###### In Which Situations Is It Used

Used in address definitions
