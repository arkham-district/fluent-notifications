<?php

declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Translation Mode
    |--------------------------------------------------------------------------
    |
    | When true, notification keys are passed through Laravel's __() helper.
    | When false, keys are used as literal strings.
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
    | Session Keys
    |--------------------------------------------------------------------------
    |
    | Session keys used by toast and alert channels.
    |
    */
    'session' => [
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
