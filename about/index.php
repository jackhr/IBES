<?php

include_once '../includes/env.php';

$title_override = "About $company_name: Your Trusted Partner for Island Adventures";
$page = "about";
$description = "Learn about $company_name. We offer quality vehicles and reliable rentals for a convenient stay. Learn our history and commitment to customer satisfaction.";
$structured_data = [
    [
        "@context" => "https://schema.org",
        "@type" => "AboutPage",
        "name" => "$company_name | About",
        "description" => $description,
        "url" => "https://$www_domain/about/",
        "publisher" => [
            "@type" => "Organization",
            "name" => $company_name,
            "logo" => "https://$www_domain/logo.avif",
            "url" => "https://$www_domain/"
        ]
    ]
];

include_once '../includes/header.php';

?>

<section class="general-header">
    <h1>About Ibes Car Rental</h1>
</section>

<section id="about-section">
    <div class="inner">
        <div class="about-panel">
            <img src="/assets/images/misc/Freepik-whiteSUV-Palms-SandyBeach-Couple-09.jpg" alt="Picture of a couple in front of their parked car down by the beach.">
            <div>
                <h2>WELCOME TO <?php echo $company_name; ?></h2>
                <p><?php echo $company_name; ?> has been servicing the transport industry in Antigua for years and we are dedicated to growing our company as the tourism sector and economy of Antigua & Barbuda expands.</p>
                <p>Whether you’re a new visitor to our islands or a national returning for a family gathering, we welcome all. <?php echo $company_name; ?> is here to provide quality transportation services customized to your specific needs.</p>
                <em>— Tristan Hughes, Owner of <?php echo $company_name; ?></em>
            </div>
        </div>

        <div class="about-panel">
            <img src="/assets/images/misc/Harbour-NelsonsDockyard-Air-JMR18809.jpg" alt="Aerial view of English Harbour">
            <div>
                <h2>WELCOME TO ANTIGUA</h2>
                <p>Antigua has so much to offer our visitors. We at <?php echo $company_name; ?> want to make your visit one of discovery and exploration. Please don’t hesitate to ask us for guidance – we will do our best to point you in the right direction.</p>
                <ul>
                    <li>365 Beaches to explore</li>
                    <li>World Renowned Nelson’s Dockyard National Park</li>
                    <li>Fig Tree Drive & The Rainforest</li>
                    <li>Nightlife & Entertainment</li>
                    <li>So much more…</li>
                </ul>
            </div>
        </div>
    </div>
</section>


<?php include_once '../includes/footer.php'; ?>