<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Distillery Filter Configuration
    |--------------------------------------------------------------------------
    |
    | You can setup the default filter namespace, giving you full control
    | over where the filters for models will be generated.
    |
    */

    'filters' => [
        'namespace' => 'App\\Filters',
    ],

    /*
    |--------------------------------------------------------------------------
    | Distillery Model Configuration
    |--------------------------------------------------------------------------
    |
    | You can setup the default eloquent model namespace, so you can use
    | class short names during filter generation.
    |
    */

    'models' => [
        'namespace' => 'App\\Models',
    ],

    /*
    |--------------------------------------------------------------------------
    | Distillery Resource Configuration
    |--------------------------------------------------------------------------
    |
    | By default your models are transformed into Eloquent: API Resources.
    | If no resource exists the models won't be mapped and collection will
    | act as if this is disabled.
    |
    */

    'resource' => [
        'enabled' => true,
        'namespace' => 'App\\Http\\Resources',
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Config
    |--------------------------------------------------------------------------
    |
    | Distillery comes with a standard route for filtering models that your
    | API can consume. The functionality is disasbled by default.
    |
    | ie.: model \App\Models\User can be used on a route:
    |
    |   /distill/users?page=2&limit15
    |
    | Models that are allowed to be filltered need to be setup in the config.
    |
    | These middleware will be assigned to every distillery route, giving you
    | the chance to add your own middleware to this list or change any of
    | the existing middleware. Or, you can simply stick with this list.
    |
    */

    'routing' => [

        'enabled' => false,

        'path' => 'distill',

        'middleware' => [
            'web'
        ],

        'models' => [
            //
        ]
    ],

    /*
    |--------------------------------------------------------------------------
    | Distillery Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Enable / disable caching paginated results and set cache time in minutes.
    |
    */

    'cache' => [

        'enabled' => false,

        'time' => 60
    ]

];
