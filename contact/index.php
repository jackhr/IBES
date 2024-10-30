<?php

include_once '../includes/env.php';

$title_override = "Contact $company_name for Your Rental Needs Today!";
$page = "contact";
$description = "Get in touch with $company_name in Antigua via phone, email, or our contact form. We're open 7 days a week to assist you with your car rental needs.";
$structured_data = [
    [
        "@context" => "https://schema.org",
        "@type" => "ContactPage",
        "name" => "$company_name | Contact",
        "description" => $description,
        "url" => "https://$www_domain/contact/"
    ],
    [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "name" => $company_name,
        "description" => "Rent affordable and well-maintained cars in Antigua and Barbuda.",
        "image" => "https://$www_domain/logo.avif",
        "url" => "https://$www_domain/",
        "address" => [
            "@type" => "PostalAddress",
            "streetAddress" => "Antigua road",
            "addressLocality" => "St. John's",
            "addressRegion" => "Antigua",
            "postalCode" => "",
            "addressCountry" => "AG"
        ],
        "telephone" => "+1-268-773-2900",
        "contactPoint" => [
            "@type" => "ContactPoint",
            "telephone" => "+1-268-773-2900",
            "contactType" => "Customer Service",
            "availableLanguage" => "English"
        ],
        "openingHours" => "Mo-Su 08:00-18:00"
    ]
];


include_once '../includes/header.php';

?>

<section class="general-header">
    <h1>contact</h1>
</section>

<section id="contact-card-section">
    <div class="inner">
        <h2><?php echo $company_name; ?></h2>

        <div class="contact-brief-info">
            <span>Antigua road</span>
            <span>St. John’s</span>
            <span>Antigua, WI</span>
        </div>

        <div>
            <div class="contact-link">
                <span>Phone:</span>
                <a href="tel:+1 (268) 773-2900">+1 (268) 773-2900</a>
            </div>
            <div class="contact-link">
                <span>Email:</span>
                <a href="mailto:info@<?php echo $domain; ?>">info@<?php echo $domain; ?></a>
            </div>
        </div>

        <div class="contact-brief-info">
            <span>Service Hours</span>
            <span>Monday to Sunday</span>
            <span>8:00 am to 6:00 pm</span>
        </div>
    </div>
</section>

<section id="contact-form-section">
    <div class="inner">
        <h2>SEND US AN EMAIL</h2>
        <form action="">
            <div class="left">
                <div class="input-container">
                    <label for="contact-message">Your Message</label>
                    <textarea name="message" id="contact-message" cols="30" rows="10" placeholder="Enter your message..."></textarea>
                </div>
            </div>
            <div class="right">
                <div class="input-container">
                    <label for="contact-name">Name *</label>
                    <input id="contact-name" name="name" type="text" placeholder="Enter your name">
                </div>
                <div class="input-container">
                    <label for="contact-email">Email *</label>
                    <input id="contact-email" name="email" type="text" placeholder="email@domain.com">
                </div>
                <button type="submit">SUBMIT</button>
            </div>
        </form>
    </div>
</section>


<?php include_once '../includes/footer.php'; ?>