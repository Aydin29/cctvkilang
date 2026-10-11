<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Performance Optimization Settings
    |--------------------------------------------------------------------------
    |
    | This configuration file contains settings for optimizing the performance
    | of the CCTV monitoring application.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Cache Settings
    |--------------------------------------------------------------------------
    |
    | Cache duration settings for various application components.
    |
    */
    'cache' => [
        'locations' => 3600, // 1 hour cache for CCTV locations
        'statistics' => 300, // 5 minutes cache for statistics
        'system_status' => 60, // 1 minute cache for system status
    ],

    /*
    |--------------------------------------------------------------------------
    | Pagination Settings
    |--------------------------------------------------------------------------
    |
    | Number of items per page for various listings.
    |
    */
    'pagination' => [
        'cctvs' => 15,
        'dashboard_cctvs' => 10,
        'live_feeds' => 8,
        'recent_activity' => 8,
    ],

    /*
    |--------------------------------------------------------------------------
    | Query Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for database query optimization.
    |
    */
    'query_optimization' => [
        'use_single_statistics_query' => true,
        'cache_frequent_queries' => true,
        'limit_eager_loading' => true,
    ],

    /*
    |--------------------------------------------------------------------------
    | Streaming Optimization
    |--------------------------------------------------------------------------
    |
    | Settings for streaming performance optimization.
    |
    */
    'streaming' => [
        'playlist_timeout' => 0, // No blocking wait for playlist files
        'max_concurrent_streams' => 10,
    ],

];
