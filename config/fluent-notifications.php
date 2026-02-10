<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Mode
    |--------------------------------------------------------------------------
    |
    | When true, title and message are passed through Laravel's __() helper.
    | When false, they are used as literal strings.
    |
    */
    'translate' => true,

    /*
    |--------------------------------------------------------------------------
    | Default Channels
    |--------------------------------------------------------------------------
    |
    | The default channels used when via() is not called.
    |
    */
    'default_channels' => ['toast'],

    /*
    |--------------------------------------------------------------------------
    | Default Type
    |--------------------------------------------------------------------------
    |
    | The default notification type when no type method is called.
    |
    */
    'default_type' => 'info',

    /*
    |--------------------------------------------------------------------------
    | Flash Keys
    |--------------------------------------------------------------------------
    |
    | Inertia flash keys used by toast and alert channels.
    |
    */
    'flash' => [
        'toasts' => 'toasts',
        'alerts' => 'alerts',
    ],

    /*
    |--------------------------------------------------------------------------
    | Queue Configuration
    |--------------------------------------------------------------------------
    |
    | Control whether notifications should be queued by default.
    |
    */
    'queue' => [
        'enabled' => false,
        'connection' => null,
        'queue' => null,
    ],
];
