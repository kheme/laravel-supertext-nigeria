<?php

return [
    'access' => [
        // Your supertextng.com account username
        'username' => env('SUPERTEXTNG_USERNAME'),

        // Your supertextng.com account password
        'password' => env('SUPERTEXTNG_PASSWORD'),
    ],

    'settings' => [
        'ignore_dnd' => env('SUPERTEXTNG_IGNORE_DND', 'yes'),

        // The sender ID to appear on recipient's phone.
        // Maximum of 11 characters as usual.
        // If more than 11 characters, the first 11 characters will be used.
        'sender' => env('SUPERTEXTNG_SENDER'),
    ]
];