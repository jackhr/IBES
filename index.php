<?php

declare(strict_types=1);

include_once __DIR__ . '/includes/app-config.php';

$title_override = 'Affordable Antigua Car Rental for Your Perfect Island Adventure';
$page = 'index';
$description = "$company_name offers affordable, well-maintained vehicles. Enjoy online booking and exceptional customer service. Rent a car in Antigua today!";
$structured_data = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'WebPage',
        'name' => $company_name,
        'description' => $description,
        'url' => "https://$www_domain/",
        'publisher' => [
            '@type' => 'Organization',
            'name' => $company_name,
            'logo' => "https://$www_domain/assets/images/logo.avif",
        ],
    ],
    [
        '@context' => 'https://schema.org',
        '@type' => 'LocalBusiness',
        'name' => $company_name,
        'description' => 'Rent affordable and well-maintained cars in Antigua and Barbuda.',
        'image' => "https://$www_domain/assets/images/logo.avif",
        'url' => "https://$www_domain/",
        'address' => [
            '@type' => 'PostalAddress',
            'streetAddress' => 'Ibes Car Rental',
            'addressLocality' => 'Coolidge',
            'addressRegion' => 'St. George',
            'postalCode' => '',
            'addressCountry' => 'AG',
        ],
        'telephone' => '+1-268-773-2900',
        'contactPoint' => [
            '@type' => 'ContactPoint',
            'telephone' => '+1-268-773-2900',
            'contactType' => 'Customer Service',
            'availableLanguage' => 'English',
        ],
        'openingHours' => 'Mo-Su 08:00-18:00',
    ],
];
$react_route = '/';

include_once __DIR__ . '/includes/react-shell.php';
