<?php

declare(strict_types=1);

include_once __DIR__ . '/../includes/app-config.php';

$title_override = "FAQs About $company_name in Antigua - Your Questions Answered";
$page = 'faq';
$description = "Find answers to questions about $company_name rental requirements, payment options, insurance, child safety seats, driving in Antigua, and more.";
$structured_data = [
    [
        '@context' => 'https://schema.org',
        '@type' => 'FAQPage',
        'mainEntity' => [
            [
                '@type' => 'Question',
                'name' => 'Is insurance included in my rental agreement?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Insurance is optional, but recommended. Without insurance, the renter is responsible for full damages. With insurance, renter liability is reduced according to agreement terms.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is required when I rent a car?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "A valid driver's license and Antigua & Barbuda temporary license are required.",
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Will there be a fee if I have to cancel my reservation?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => "No, provided that $company_name has not dispatched a vehicle at the time of cancellation.",
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'When does the rental time start and stop?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'The rental starts at vehicle handover and ends at the agreed return time.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the rental time period?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Daily rentals are based on 24-hour periods. Weekly and monthly terms are available.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Which side of the road do you drive on in Antigua? Can I drive my rental vehicle off road?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'In Antigua & Barbuda, traffic drives on the left. Rentals should remain on regular roads.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What methods of payment do you accept?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'We accept cash and major credit cards including Visa and MasterCard.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What exchange rate do you use from USD to ECD?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'The local currency is ECD. Payments may be accepted in USD and ECD at the local fixed conversion rate.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Will my rental payment cover for damages?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Damage coverage applies when optional collision insurance is selected in the rental agreement.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can anyone else drive my rental vehicle?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes. Additional drivers can be added to the rental agreement.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Do you provide child safety seats?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes, complimentary child seats are offered subject to availability.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'How do I navigate around the island?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'GPS support and maps may be provided when available.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'How should I return my vehicle?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Return the vehicle in similar condition and fuel level as at pickup unless otherwise agreed.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the total cost of renting a car?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Total cost includes base rate, insurance, taxes, and selected extras.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What is the fuel policy?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Most agreements require returning the vehicle with equivalent fuel level.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What should I do if I have an accident or mechanical issue?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Contact emergency services when needed, then contact Ibes Car Rental using your agreement details.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'What happens if I return the car late?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Late returns may incur additional charges. Contact the team as early as possible if delayed.',
                ],
            ],
            [
                '@type' => 'Question',
                'name' => 'Can I rent without a reservation?',
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => 'Yes, but advance booking is recommended, especially during peak periods.',
                ],
            ],
        ],
    ],
];
$react_route = '/faq';

include_once __DIR__ . '/../includes/react-shell.php';
