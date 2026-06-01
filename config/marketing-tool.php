<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    | Define the middleware protecting the marketing dashboard. By default,
    | it uses Laravel's web group and Laravel Nova's native authentication guard.
    */
    'middleware' => [
        'web',
        \Laravel\Nova\Http\Middleware\Authenticate::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefix
    |--------------------------------------------------------------------------
    | Access URL: your-domain.uz/marketing-dashboard
    */
    'prefix' => 'marketing-dashboard',

    /*
    |--------------------------------------------------------------------------
    | Models Mapping
    |--------------------------------------------------------------------------
    | Map the Eloquent models used for statistics queries.
    */
    'models' => [
        'user' => \App\Models\User::class,
        'user_activity' => class_exists(\App\Models\UserActivity::class) 
            ? \App\Models\UserActivity::class 
            : (\App\Models\UserActive::class),
        'app_discovery_source' => class_exists(\App\Models\AppDiscoverySource::class) 
            ? \App\Models\AppDiscoverySource::class 
            : null,
        'user_app_discovery_source' => class_exists(\App\Models\UserAppDiscoverySource::class) 
            ? \App\Models\UserAppDiscoverySource::class 
            : null,
        'transfer' => \App\Models\Transfer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Fields Mapping
    |--------------------------------------------------------------------------
    | Map database columns used for querying stats.
    */
    'fields' => [
        'user_activity' => [
            'date_column' => class_exists(\App\Models\UserActivity::class) ? 'day' : 'active_date',
        ],
    ],
];
