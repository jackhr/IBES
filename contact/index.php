<?php

declare(strict_types=1);

include_once __DIR__ . '/../includes/app-config.php';

$title_override = "Contact $company_name for Your Rental Needs Today!";
$page = 'contact';
$description = "Get in touch with $company_name in Antigua via phone, email, or our contact form. We're open 7 days a week to assist you with your car rental needs.";
$structured_data = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'ContactPage',
        'name' => "$company_name | Contact",
        'description' => $description,
        'url' => "https://$www_domain/contact/",
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
$react_route = '/contact';

include_once __DIR__ . '/../includes/react-shell.php';
