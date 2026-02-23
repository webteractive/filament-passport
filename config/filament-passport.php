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
    | Set enabled to false to disable all view registration, or set
    | individual views to a custom view name or null to skip that
    | specific view while keeping the others.
    |
    */

    'views' => [
        'enabled' => true,

        'authorization' => 'filament-passport::authorize',
        'device_authorization' => 'filament-passport::device.authorize',
        'device_user_code' => 'filament-passport::device.user-code',
    ],

];
