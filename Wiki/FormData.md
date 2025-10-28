###### How to set DATA to the form

There are several options.

1. Db Table
2. Row
3. SQL
4. QUERY

1. Db Table

```php
'data' => [
        /**
        * OPTIONAL BUT
        * This connection is need for ALL SQL QUERIES in form generator array.
        * While using rows or query for formdata, if you need sql operations in form generator array, you must add this.
        */
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
        /**
         * Data Structure Start For Db Table usage
         * There are several other data getting formats, they are explaining with other data title
         * if data comes from table id must set here
         * System does not apply any secure filter, please provide filtered data 
         */
        'id' => '7',// or $_GET['id'] etc...
        /**
         * if it doesn't set, the system will use id column name
         */
        'id_column_name' => 'id',
        /**
         * if data comes from table it must set here
         */
        'table' => 'address',
        /// Data Structure Finish
    ],
```

2. Rows

```php
'data' => [          
        'row' =>[
        'id' => '7',
    'type' => '1',
    'user_id' => '8015',
    'address_identification' => 'Work Adress',
    'name' => 'Joe',
    'surname' => 'DOE',
    'address' => 'Test strasse berlin',
    'postal_code' => '28100',
    'country' => '',
    'province' => '0',
    'county' => '0',
    'district' => '0',
    'neighbourhood' => '0',
    'phone' => '',
    'mobile_phone' => '5542856789',
    'mail' => null,
    'invoice_type' => '1',
    'identification_number' => '3514950',
    'nationality_tc_or_not' => '1',
    'company_name' => '',
    'tax_department' => '',
    'tax_number' => '',
    'is_e_invoice_user' => '2',
] ,
 /**
        * OPTIONAL BUT
        * This connection is need for ALL SQL QUERIES in form generator array.
        * While using rows or query for formdata, if you need sql operations in form generator array, you must add this.
        */
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
        ]
        /// Data Structure Finish
    ],
```

3. SQL

```php
'data' => [          
        'sql' =>"SELECT * FROM address WHERE id='7'",
        /**
        * OPTIONAL BUT
        * This connection is need for ALL SQL QUERIES in form generator array.
        * While using rows or query for formdata, if you need sql operations in form generator array, you must add this.
        */
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
        ]
        /// Data Structure Finish
    ],
```

4. QUERY

```php
'data' => [          
        'query' => DB::query("SELECT * FROM address WHERE id='7'"),
        /**
        * OPTIONAL BUT
        * This connection is need for ALL SQL QUERIES in form generator array.
        * While using rows or query for formdata, if you need sql operations in form generator array, you must add this.
        */
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
        ]
        /// Data Structure Finish
    ],
```

### TIP

If you use 'from' index, it will process little faster.
```php
'data' => [
        'from' => 'row',
```
