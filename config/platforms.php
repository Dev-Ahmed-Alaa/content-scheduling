<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Character Limits
    |--------------------------------------------------------------------------
    |
    | Default character limits for each platform. These are used as fallbacks
    | and for reference. The actual limits are stored in the database.
    |
    */

    'character_limits' => [
        'x' => 280,
        'instagram' => 2200,
        'linkedin' => 3000,
        'facebook' => 63206,
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Platforms
    |--------------------------------------------------------------------------
    |
    | The default platforms to seed into the database.
    |
    */

    'defaults' => [
        [
            'name' => 'X (Twitter)',
            'type' => 'x',
            'character_limit' => 280,
            'is_active' => true,
        ],
        [
            'name' => 'Instagram',
            'type' => 'instagram',
            'character_limit' => 2200,
            'is_active' => true,
        ],
        [
            'name' => 'LinkedIn',
            'type' => 'linkedin',
            'character_limit' => 3000,
            'is_active' => true,
        ],
        [
            'name' => 'Facebook',
            'type' => 'facebook',
            'character_limit' => 63206,
            'is_active' => true,
        ],
    ],

];
