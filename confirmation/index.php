<?php

session_start();

include_once '../includes/connection.php';

$title_suffix = "Confirmation";
$page = "confirmation";
$description = "Thank you for booking with $company_name. Your reservation has been requested. Review your order details, including vehicle, add-ons, and estimated total.";
$extra_css = "reservation";
$structured_data = [
    [
        "@context" => "https://schema.org",
        "@type" => "WebPage",
        "name" => $company_name,
        "description" => $description,
        "url" => "https://$www_domain/",
        "publisher" => [
            "@type" => "Organization",
            "name" => $company_name,
            "logo" => "https://$www_domain/assets/images/logo.avif"
        ]
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
            "streetAddress" => "Ibes Car Rental",
            "addressLocality" => "Coolidge",
            "addressRegion" => "St. George",
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

$key = isset($_GET['key']) ? $_GET['key'] : null;
$jack_testing = (isset($_GET['test']) && ($_GET['test'] == 'true'));

if (isset($key)) {
    // Get the order request
    $order_request_query = "SELECT * FROM order_requests WHERE `key` = '{$key}'";
    $order_request_result = mysqli_query($con, $order_request_query);
    $order_request = mysqli_fetch_assoc($order_request_result);
}

if (isset($order_request)) {
    // Get the add-ons
    $order_request_add_on_query = "SELECT * FROM order_request_add_ons WHERE `order_request_id` = {$order_request['id']}";
    $order_request_add_on_result = mysqli_query($con, $order_request_add_on_query);
    $add_ons = [];
    while ($add_on = mysqli_fetch_assoc($order_request_add_on_result)) {
        $add_on_query = "SELECT * FROM add_ons WHERE `id` = {$add_on['add_on_id']}";
        $add_on_result = mysqli_query($con, $add_on_query);
        $add_ons[] = mysqli_fetch_assoc($add_on_result);
    }

    // Get the vehicle
    $vehicle_query = "SELECT * FROM vehicles WHERE `id` = {$order_request['car_id']}";
    $vehicle_result = mysqli_query($con, $vehicle_query);
    $vehicle = mysqli_fetch_assoc($vehicle_result);

    // Get the vehicle discount
    $discount_query = "SELECT * FROM vehicle_discounts WHERE `vehicle_id` = {$order_request['car_id']}";
    $discount_result = mysqli_query($con, $discount_query);
    $discount = mysqli_fetch_assoc($discount_result);

    // Get the contact infos
    $contact_info_query = "SELECT * FROM contact_info WHERE `id` = {$order_request['contact_info_id']}";
    $contact_info_result = mysqli_query($con, $contact_info_query);
    $contact_info = mysqli_fetch_assoc($contact_info_result);

    $structured_data[] = [
        "@context" => "https://schema.org",
        "@type" => "Order",
        "orderNumber" => $order_request['id'],
        "orderStatus" => $order_request['confirmed'] ? "https://schema.org/OrderPickupAvailable" : "https://schema.org/OrderProcessing",
        "orderDate" => $order_request['created_at'],
        "acceptedOffer" => [
            "@type" => "Offer",
            "name" => "Car rental for {$order_request['days']} days",
            "description" => "{$vehicle['type']} with room for {$vehicle['people']} people for {$order_request['days']} days",
            "priceCurrency" => "USD",
            "price" => $order_request['sub_total'],
            "itemOffered" => [
                "@type" => "Product",
                "name" => $vehicle['name'],
                "description" => "{$vehicle['type']} with room for {$vehicle['people']}",
                "image" => "https://$www_domain/assets/images/vehicles/{$vehicle['slug']}.avif",
                "brand" => [
                    "@type" => "Brand",
                    "name" => explode(" ", $vehicle['name'])[0]
                ],
                "offers" => [
                    "@type" => "Offer",
                    "priceCurrency" => "USD",
                    "price" => $vehicle['base_price_USD'],
                    "eligibleQuantity" => [
                        "@type" => "QuantitativeValue",
                        "value" => $order_request['days'],
                        "unitCode" => "DAY"
                    ],
                    "itemOffered" => [
                        "@type" => "Product",
                        "name" => $vehicle['name']
                    ],
                    "additionalProperty" => [
                        [
                            "@type" => "PropertyValue",
                            "name" => "Transmission",
                            "value" => $vehicle['manual'] == "1" ? "Manual" : "Automatic"
                        ],
                        [
                            "@type" => "PropertyValue",
                            "name" => "Air Conditioning",
                            "value" => $vehicle['ac'] == "1" ? "Yes" : "No"
                        ],
                        [
                            "@type" => "PropertyValue",
                            "name" => "4WD",
                            "value" => $vehicle['4wd'] == "1" ? "Yes" : "No"
                        ],
                        [
                            "@type" => "PropertyValue",
                            "name" => "Seats",
                            "value" => $vehicle['people']
                        ],
                        [
                            "@type" => "PropertyValue",
                            "name" => "Doors",
                            "value" => $vehicle['doors']
                        ]
                    ]
                ]
            ],
            "addOn" => [
                "@type" => "Offer",
                "name" => "Add-ons",
                "itemOffered" => []
            ]
        ],
        "customer" => [
            "@type" => "Person",
            "name" => $contact_info['first_name'] . " " . $contact_info['last_name'],
            "email" => $contact_info['email'],
            "telephone" => $contact_info['phone'],
            "address" => [
                "@type" => "PostalAddress",
                "streetAddress" => $contact_info['street'],
                "addressLocality" => $contact_info['town_or_city'],
                "addressRegion" => $contact_info['state_or_county'],
                "addressCountry" => $contact_info['country_or_region']
            ]
        ]
    ];

    // Adding Add-ons as part of the order
    foreach ($add_ons as $add_on) {
        $structured_data[2]['acceptedOffer']['addOn']['itemOffered'][] = [
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
}

include_once '../includes/header.php';

?>

<section class="general-header">
    <h1>Order Confirmation</h1>
</section>

<?php if (isset($order_request)) { ?>
    <section id="confirmation-section">
        <div class="inner">
            <h2>Thank you! Your order has been requested.</h2>

            <div class="reservation-flow-container">
                <?php include '../includes/reservation-summary.php'; ?>
                <div class="right">
                    <div class="order-header">
                        <span>Order Number:</span>
                        <span><?php echo $order_request['id']; ?></span>
                    </div>

                    <h6>Summary</h6>

                    <div id="order-summary">
                        <div class="order-summary-item itinerary">
                            <div class="left">
                                <h6>Pick Up</h6>
                                <p><?php echo date('F d, Y h:i A', strtotime($order_request['pick_up'])); ?></p>
                            </div>
                            <div class="right">
                                <h6>Drop Off</h6>
                                <p><?php echo date('F d, Y h:i A', strtotime($order_request['drop_off'])); ?></p>
                            </div>
                        </div>
                        <div class="order-summary-item vehicle">
                            <div class="left">
                                <h6>Vehicle Type</h6>
                                <p><?php echo "{$vehicle['name']} {$vehicle['type']}"; ?></p>
                            </div>
                            <div class="right">
                                <h6>Add-ons</h6>
                                <p><?php
                                    $counter = 0;
                                    foreach ($add_ons as $add_on) {
                                        $prefix = $counter > 0 ? ", " : "";
                                        $counter++;
                                        echo "<span>{$prefix}{$add_on['name']}</span>";
                                    } ?>
                                </p>
                            </div>
                        </div>
                        <div class="order-summary-item contact-info">
                            <div class="left">
                                <h6>Your Information</h6>
                                <p>
                                    <?php
                                    echo "{$contact_info['first_name']} {$contact_info['last_name']}<br>";
                                    echo "{$contact_info['street']}<br>";
                                    echo "{$contact_info['town_or_city']}, {$contact_info['state_or_county']}<br>";
                                    echo "{$contact_info['country_or_region']}<br>";
                                    echo "{$contact_info['phone']}<br>";
                                    echo "{$contact_info['email']}<br>";
                                    ?>
                                </p>
                            </div>
                            <div class="right">
                                <h6>Payment Information</h6>
                                <p>Estimated Total - <?php echo $estimated_total; ?></p>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
<?php } ?>

<section id="key-submit-section">
    <div class="inner">
        <?php if (!isset($key)) {
            echo "<h2>Find Your Reservation</h2>";
        } else {
            if (!isset($order_request)) {
                echo "<h2>No rservation found with key <span>\"{$key}\"</span></h2>";
            }
            echo "<h2>Find A Different Reservation</h2>";
        } ?>
        <form id="confirmation-key-form" method="get">
            <div class="input-container">
                <label for="key">Confirmation Key</label>
                <input type="text" name="key" placeholder="example-key-123">
                <input type="hidden" name="test" value="true">
            </div>
            <button type="submit">Submit</button>
        </form>
    </div>
</section>

<?php include_once '../includes/footer.php'; ?>