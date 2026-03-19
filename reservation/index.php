<?php

declare(strict_types=1);

include_once __DIR__ . '/../includes/app-config.php';

$title_override = "Reserve Your Island Adventure with $company_name in Antigua!";
$page = 'reservation';
$description = 'Start your reservation with our booking experience and secure transportation for your stay in Antigua.';
$structured_data = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => "$company_name | Reservation",
        'description' => $description,
        'url' => "https://$www_domain/reservation/",
        'publisher' => [
            '@type' => 'Organization',
            'name' => $company_name,
            'logo' => "https://$www_domain/assets/images/logo.avif",
            'url' => "https://$www_domain/",
        ],
    ],
];
$react_route = '/reservation';

include_once __DIR__ . '/../includes/react-shell.php';
