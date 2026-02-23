<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    |
    | Configure the navigation group and sort order for the Passport resources.
    |
    */

    'navigation' => [
        'group' => 'OAuth',
        'sort' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Resources
    |--------------------------------------------------------------------------
    |
    | Toggle individual resources on/off and customize their labels.
    |
    */

    'resources' => [
        'client' => [
            'enabled' => true,
            'label' => 'Client',
            'plural_label' => 'Clients',
        ],
        'access_token' => [
            'enabled' => true,
            'label' => 'Access Token',
            'plural_label' => 'Access Tokens',
        ],
        'auth_code' => [
            'enabled' => true,
            'label' => 'Auth Code',
            'plural_label' => 'Auth Codes',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Authorization Views
    |--------------------------------------------------------------------------
    |
    | This package registers default OAuth consent views for Passport.
    | Set enabled to false if you prefer to register your own views
    | via Passport::authorizationView() or Passport::viewPrefix().
    |
    */

    'views' => [
        'enabled' => true,
    ],

];
