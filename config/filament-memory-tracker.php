<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Store
    |--------------------------------------------------------------------------
    |
    | Define the cache store used to track memory usage.
    | See: https://laravel.com/docs/8.x/cache#configuration
    |
    */

    'cache_store' => env('CACHE_DRIVER', 'file'),

    /*
    |--------------------------------------------------------------------------
    | Trackers to display
    |--------------------------------------------------------------------------
    |
    | A list of trackers names to be displayed in the dashboard.
    | They must be the same used in your MemoryTracker() instance.
    |
    */

    'trackers' => [],

    /*
    |--------------------------------------------------------------------------
    | Date format
    |--------------------------------------------------------------------------
    |
    | In which format the dates (with time) will be displayed.
    |
    */

    'date_format' => 'j F Y @ H:i:s',

];
