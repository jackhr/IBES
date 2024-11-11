<?php

include_once '../includes/env.php';

$title_override = "FAQs About $company_name in Antigua - Your Questions Answered";
$page = "faq";
$description = "Find answers to questions about $company_name Rental requirements, payment options, insurance, child safety seats, driving in Antigua, and more.";
$structured_data = [
    [
        "@context" => "https://schema.org",
        "@type" => "FAQPage",
        "mainEntity" => [
            [
                "@type" => "Question",
                "name" => "Is insurance included in my rental agreement?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "For the insurance policy, it is optional but we do recommend that you rent with the additional insurance because if you do not rent with the insurance and you damage the vehicle you are responsible to cover all damages for the vehicle. Whereas if you rent with the insurance and any damage occur you are only allowed to pay $1500 and the insurance will cover the rest."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What is required when I rent a car?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "A valid Driver's License and Antigua & Barbuda Temporary License which is valid for 3 months at a cost of USD$20.00."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Will there be a fee if I have to cancel my reservation?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "No, provided that $company_name has not dispatched a vehicle at the time you cancel."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "When does the rental time start and stop?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "The start time of the rental period shall be at the actual delivery and acceptance by the individual of the vehicle. The ending time shall be at the end of rental time period. $company_name will make prior arrangements for pick ups and drop offs at locations most convenient for you."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What is the rental time period?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Daily Rentals: Daily rentals are based on a 24-hour period.Weekly Rentals: Weekly rentals are based on a week to week period and the same for Monthly rentals.Long Term/Leasing: Terms will be arranged.If the car is returned after the 24-hour period, then an additional charge is assessed."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Which side of the road do you drive on in Antigua? Can I drive my rental vehicle off road?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "In Antigua & Barbuda, we drive on the left side of the road. Our rental vehicles should stay on main roads vs off-roads as they are not off-road vehicles."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What methods of payment do you accept?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "We accept cash, and major Credit Cards - Visa & MasterCard."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What exchange rate do you use from \$USD to \$ECD?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "The local currency in Antigua is Eastern Caribbean Dollars (ECD). The fixed bank rate for conversion of USD to ECD is US$1.00 = EC$2.7169. We accept payment in both USD and ECD."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Will my rental payment cover for damages?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Only if accident collision insurance is taken at an additional daily rate indicated in your rental agreement. This is optional."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Can anyone else drive my rental vehicle?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Yes. Additional driver(s) can be added to rental agreement."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Do you provide child safety seats?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Yes. This option is a complimentary offer (upon availability) and is recommended by Law for children under the age of four."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "How do I navigate around the Island?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "A complementary offer (upon availability) is given for GPS turn-by-turn navigation. Also, maps are available in your vehicle."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "How should I return my vehicle?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "All vehicles should be returned in the same condition as when the rental vehicle were delivered or picked up. If the vehicle is not fueled, you will be charged a fuel charge."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What is the total cost of renting a car?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "The total includes the base rental fee plus insurance, taxes, and any extras. Request a cost breakdown to avoid surprises."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What is the fuel policy?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Most agreements require you to return the car with a full tank. Returning it with less may incur refueling charges."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What should I do if I have an accident or mechanical issue?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Contact emergency services if needed, then call  Car Ibes Rental using the number in your agreement for assistance."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "What happens if I return the car late?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Late returns may incur extra charges. Contact Ibes Car Rental ahead of time if you're running late."
                ]
            ],
            [
                "@type" => "Question",
                "name" => "Can I rent without a reservation?",
                "acceptedAnswer" => [
                    "@type" => "Answer",
                    "text" => "Yes, but it's recommended to book in advance, especially during peak seasons."
                ]
            ],
        ]
    ]
];

include_once '../includes/header.php';

?>

<section class="general-header">
    <h1>Frequently Asked Questions</h1>
</section>

<section id="faq-section">
    <div class="inner">
        <div id="faq-header">
            <span>Select a question to see the answer</span>
            <div></div>
        </div>

        <div id="faqs">
            <?php foreach ($structured_data[0]['mainEntity'] as $faq) { ?>
                <div class="faq">
                    <div class="faq-top">
                        <div class="faq-toggle"></div>
                        <span class="faq-question"><?php echo $faq['name']; ?></span>
                    </div>
                    <p class="faq-answer"><?php echo $faq['acceptedAnswer']['text']; ?></p>
                </div>
            <?php } ?>
        </div>
    </div>
</section>


<?php include_once '../includes/footer.php'; ?>