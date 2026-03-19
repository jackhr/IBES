<?php

declare(strict_types=1);

include_once __DIR__ . '/../includes/app-config.php';

$title_override = "About $company_name: Your Trusted Partner for Island Adventures";
$page = 'about';
$description = "Learn about $company_name. We offer quality vehicles and reliable rentals for a convenient stay. Learn our history and commitment to customer satisfaction.";
$structured_data = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'AboutPage',
        'name' => "$company_name | About",
        'description' => $description,
        'url' => "https://$www_domain/about/",
        'publisher' => [
            '@type' => 'Organization',
            'name' => $company_name,
            'logo' => "https://$www_domain/assets/images/logo.avif",
            'url' => "https://$www_domain/",
        ],
    ],
];
$react_route = '/about';

include_once __DIR__ . '/../includes/react-shell.php';
