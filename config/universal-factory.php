<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Namespace for Universal Factories
    |--------------------------------------------------------------------------
    |
    | This value defines the default namespace for the universal factories. You can
    | change it to fit your application's needs.
    |
    */
    'default_namespace' => 'App\\Factories\\',

    /*
    |--------------------------------------------------------------------------
    | Universal Factory Method Name
    |--------------------------------------------------------------------------
    |
    | This value allows the user to specify the name of the factory method
    | provided by the HasUniversalFactory trait.

    | For example, if your source class does too much, and already has a poorly
    | designed static factory() method that we cannot just override.
    |
    */
    'method_name' => 'factory',
];
