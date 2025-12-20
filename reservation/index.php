<?php

session_start();

include_once '../includes/connection.php';

if (isset($_GET['reset-data']) && $_GET['reset-data'] == 'true') {
    session_destroy();
    header('Location: /reservation/');
}

$title_suffix = "Reservation";
$title_override = "Reserve Your Island Adventure with $company_name in Antigua!";
$page = "reservation";
$description = "Make your reservation for a hassle-free car rental experience in Antigua. Explore the island at your own pace with our reliable vehicles and excellent service.";

$see_all_vehicles = isset($_GET['itinerary']) && ($_GET['see-all-vehicles'] == 'true');

$vehicles_arr = [];

$vehicles_query = "SELECT * FROM `vehicles` WHERE `showing` = 1 ORDER BY `base_price_USD`, `name` ASC;";
$vehicles_result = mysqli_query($con, $vehicles_query);
while ($row = mysqli_fetch_assoc($vehicles_result)) $vehicles_arr[] = $row;

$add_ons_query = "SELECT * FROM add_ons";
$add_ons_result = mysqli_query($con, $add_ons_query);
while ($row = mysqli_fetch_assoc($add_ons_result)) $add_ons_arr[] = $row;

$structured_data = [];

foreach ($vehicles_arr as $structured_v) {
    $structured_data[] = [
        "@context" => "https://schema.org",
        "@type" => "Product",
        "name" => $structured_v['name'],
        "description" => $structured_v['type'] . " with room for " . $structured_v['people'] . " people.",
        "image" => "https://$www_domain/assets/images/vehicles/" . $structured_v['slug'] . ".avif",
        "brand" => [
            "@type" => "Brand",
            "name" => explode(" ", $structured_v['name'])[0]
        ],
        "offers" => [
            "@type" => "Offer",
            "price" => $structured_v['base_price_USD'],
            "priceCurrency" => "USD",
            "availability" => "https://schema.org/" . ($structured_v['showing'] == "1" ? "InStock" : "OutOfStock"),
        ],
        "additionalProperty" => [
            [
                "@type" => "PropertyValue",
                "name" => "Transmission",
                "value" => $structured_v['manual'] == "1" ? "Manual" : "Automatic"
            ],
            [
                "@type" => "PropertyValue",
                "name" => "Air Conditioning",
                "value" => $structured_v['ac'] == "1" ? "Yes" : "No"
            ],
            [
                "@type" => "PropertyValue",
                "name" => "4WD",
                "value" => $structured_v['4wd'] == "1" ? "Yes" : "No"
            ],
            [
                "@type" => "PropertyValue",
                "name" => "Seats",
                "value" => $structured_v['people']
            ],
            [
                "@type" => "PropertyValue",
                "name" => "Doors",
                "value" => $structured_v['doors']
            ]
        ]
    ];
}

foreach ($add_ons_arr as $add_on) {
    $structured_data[] = [
        "@context" => "https://schema.org",
        "@type" => "Service",
        "name" => $add_on['name'],
        "description" => strip_tags($add_on['description']),
        "offers" => [
            "@type" => "Offer",
            "price" => $add_on['cost'],
            "priceCurrency" => "USD",
            "availability" => "https://schema.org/InStock"
        ],
        "additionalProperty" => [
            [
                "@type" => "PropertyValue",
                "name" => "Fixed Price",
                "value" => $add_on['fixed_price'] == "1" ? "Yes" : "No"
            ]
        ]
    ];
}

include_once '../includes/header.php';
?>

<section class="general-header">
    <h1>Reservation</h1>
</section>

<section id="reservation-steps">
    <div class="inner">
        <script src="https://ibes-car-rental-and-taxi-service.hqrentals.app/public/car-rental/integrations/assets/integrator"></script>
        <div
            class="hq-rental-software-integration"
            data-integrator_link="https://ibes-car-rental-and-taxi-service.hqrentals.app/public/car-rental/integrations" data-brand="l8ivujtj-69ui-2yks-znlo-jubptz7rrmul"
            data-snippet="reservations"
            data-skip_language=""
            data-rate_type_uuid=""
            data-referral=""
            data-enable_auto_language_update=""
        ></div>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>