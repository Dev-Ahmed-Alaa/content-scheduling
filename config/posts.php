<?php

return [

  /*
    |--------------------------------------------------------------------------
    | Daily Schedule Limit
    |--------------------------------------------------------------------------
    |
    | Maximum number of posts a user can schedule per day. This helps prevent
    | abuse and ensures fair usage of the scheduling system.
    |
    */

  'daily_schedule_limit' => env('POSTS_DAILY_SCHEDULE_LIMIT', 10),

  /*
    |--------------------------------------------------------------------------
    | Default Pagination
    |--------------------------------------------------------------------------
    |
    | Default number of posts per page when listing posts.
    |
    */

  'per_page' => env('POSTS_PER_PAGE', 15),

  /*
    |--------------------------------------------------------------------------
    | Publishing Settings
    |--------------------------------------------------------------------------
    |
    | Settings related to the publishing process.
    |
    */

  'publishing' => [
    // Number of retry attempts for failed publishing
    'max_retries' => env('POSTS_MAX_RETRIES', 3),

    // Backoff times in seconds between retries
    'backoff' => [30, 60, 120],

    // Lock timeout in seconds for processing posts
    'lock_timeout' => env('POSTS_LOCK_TIMEOUT', 300),
  ],

  /*
    |--------------------------------------------------------------------------
    | Analytics Cache
    |--------------------------------------------------------------------------
    |
    | Cache settings for analytics queries.
    |
    */

  'analytics' => [
    // Cache TTL in seconds
    'cache_ttl' => env('ANALYTICS_CACHE_TTL', 300),

    // Default timeline range in days
    'default_timeline_days' => 30,
  ],

];
