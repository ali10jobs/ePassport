<?php

return [

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'up'],

    'allowed_methods' => ['*'],

    'allowed_origins' => [
        'https://epassport-web-mu.vercel.app',
    ],

    'allowed_origins_patterns' => [
        // Any Vercel deployment (production alias + previews) for this project.
        '#^https://epassport-[a-z0-9-]+\.vercel\.app$#',
    ],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
