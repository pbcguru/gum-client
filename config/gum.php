<?php

return [
    'api_url' => env('GUM_API_URL', 'https://local.gum.pbc.guru:8447/api'),
    'api_key' => env('GUM_API_KEY'),
    'jwt_secret' => env('GUM_JWT_SECRET'),
    'verify_ssl' => env('GUM_VERIFY_SSL', true),
];
