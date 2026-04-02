<?php

return [
    'company_name' => env('COMPANY_NAME', 'Ibes Car Rental'),
    'app_domain' => env('APP_DOMAIN', 'ibescarrental.com'),
    'app_www_domain' => env('APP_WWW_DOMAIN', 'www.ibescarrental.com'),
    'email_string' => env('EMAIL_STRING', ''),
    'contact_email_string' => env('CONTACT_EMAIL_STRING', env('EMAIL_STRING', '')),
    'testing_email_string' => env('TESTING_EMAIL_STRING'),
    'debugging_email_string' => env('DEBUGGING_EMAIL_STRING'),
    'destroy_session_after_ordering' => (bool) env('DESTROY_SESSION_AFTER_ORDERING', true),
    'under_construction' => (bool) env('UNDER_CONSTRUCTION', false),
    'visitor_tracking_enabled' => (bool) env('VISITOR_TRACKING_ENABLED', true),
    'rate_limits' => [
        'default' => [
            'max' => (int) env('RATE_LIMIT_MAX', 15),
            'window' => (int) env('RATE_LIMIT_WINDOW', 900),
        ],
        'contact' => [
            'max' => (int) env('CONTACT_RATE_LIMIT_MAX', 6),
            'window' => (int) env('CONTACT_RATE_LIMIT_WINDOW', 900),
        ],
        'contact_info' => [
            'max' => (int) env('CONTACT_INFO_RATE_LIMIT_MAX', env('RATE_LIMIT_MAX', 15)),
            'window' => (int) env('CONTACT_INFO_RATE_LIMIT_WINDOW', env('RATE_LIMIT_WINDOW', 900)),
        ],
        'taxi' => [
            'max' => (int) env('TAXI_RATE_LIMIT_MAX', 6),
            'window' => (int) env('TAXI_RATE_LIMIT_WINDOW', 900),
        ],
        'taxi_requests' => [
            'max' => (int) env('TAXI_REQUESTS_RATE_LIMIT_MAX', env('TAXI_RATE_LIMIT_MAX', 6)),
            'window' => (int) env('TAXI_REQUESTS_RATE_LIMIT_WINDOW', env('TAXI_RATE_LIMIT_WINDOW', 900)),
        ],
        'reservation' => [
            'max' => (int) env('RESERVATION_RATE_LIMIT_MAX', 8),
            'window' => (int) env('RESERVATION_RATE_LIMIT_WINDOW', 900),
        ],
        'reservation_api' => [
            'max' => (int) env('RESERVATION_API_RATE_LIMIT_MAX', 60),
            'window' => (int) env('RESERVATION_API_RATE_LIMIT_WINDOW', 300),
        ],
        'order_requests' => [
            'max' => (int) env('ORDER_REQUESTS_RATE_LIMIT_MAX', env('RESERVATION_RATE_LIMIT_MAX', 8)),
            'window' => (int) env('ORDER_REQUESTS_RATE_LIMIT_WINDOW', env('RESERVATION_RATE_LIMIT_WINDOW', 900)),
        ],
    ],
    'captcha' => [
        'enabled' => (bool) env('CAPTCHA_ENABLED', true),
        'provider' => env('CAPTCHA_PROVIDER', 'none'),
        'http_transport' => env('CAPTCHA_HTTP_TRANSPORT', 'auto'),
        'hcaptcha' => [
            'secret_key' => env('HCAPTCHA_SECRET_KEY'),
            'site_key' => env('HCAPTCHA_SITE_KEY', env('VITE_HCAPTCHA_SITE_KEY')),
            'verify_url' => env('HCAPTCHA_VERIFY_URL', 'https://api.hcaptcha.com/siteverify'),
        ],
        'recaptcha' => [
            'secret_key' => env('RECAPTCHA_SECRET_KEY'),
            'verify_url' => env('RECAPTCHA_VERIFY_URL', 'https://www.google.com/recaptcha/api/siteverify'),
        ],
    ],
];
