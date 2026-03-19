<?php

declare(strict_types=1);

include_once __DIR__ . '/../includes/app-config.php';

$title_override = "Reserve Your Island Adventure taxi service with $company_name in Antigua!";
$page = 'taxi';
$description = 'Make your taxi reservation for a hassle-free transfer experience in Antigua with our reliable vehicles and excellent service.';
$structured_data = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'Service',
        'serviceType' => 'Taxi Reservation',
        'provider' => [
            '@type' => 'LocalBusiness',
            'name' => $company_name,
            'url' => "https://$www_domain/",
        ],
        'areaServed' => [
            '@type' => 'Country',
            'name' => 'Antigua and Barbuda',
        ],
        'description' => $description,
    ],
];
$react_route = '/taxi';

include_once __DIR__ . '/../includes/react-shell.php';
