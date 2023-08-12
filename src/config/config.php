<?php

return [
    'secret' => env('HCAPTCHA_SECRET'),
    'sitekey' => env('HCAPTCHA_SITEKEY'),
    'get_config_method' => env('HCAPTCHA_CONFIG', false),
    'options' => env('HCAPTCHA_OPTIONS', ['timeout' => 30]), // http options
    'score_verification' => env('HCAPTCHA_SCORE', false), // This is an exclusive Enterprise feature
    'score_threshold' => env('HCAPTCHA_THRESHOLD', 0.7) // Any requests above this score will be considered as spam
];
