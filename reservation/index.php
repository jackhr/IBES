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

if (isset($_GET['itinerary'])) {
    $_SESSION['reservation']['itinerary'] = $_GET['itinerary'];
}

if (isset($_GET['vehicle_id'])) {
    $vehicle_query = "SELECT * FROM vehicles WHERE id = {$_GET['vehicle_id']}";
    $vehicle_result = mysqli_query($con, $vehicle_query);
    $vehicle_response = mysqli_fetch_assoc($vehicle_result);
    $vehicle_response['imgSrc'] = "/assets/images/vehicles/{$vehicle_response['slug']}.avif";
    $_SESSION['reservation']['vehicle'] = $vehicle_response;
    if (isset($_SESSION['reservation']['step'])) {
        if ($_SESSION['reservation']['step'] == 2) $_SESSION['reservation']['step'] = 1;
    }
}

if (isset($_SESSION['reservation']['itinerary'])) {
    $_SESSION['reservation']['step'] = 2;
    if (isset($_SESSION['reservation']['vehicle']) || isset($_SESSION['reservation']['add_ons'])) {
        $_SESSION['reservation']['step'] = 3;
    }
} else if (isset($_SESSION['reservation']['vehicle']) || isset($_SESSION['reservation']['add_ons'])) {
    $_SESSION['reservation']['step'] = 2;
    if (isset($_SESSION['reservation']['vehicle']) && isset($_SESSION['reservation']['add_ons']) && count($_SESSION['reservation']['add_ons']) > 0) {
        $_SESSION['reservation']['step'] = 3;
    } else {
        if (!isset($_SESSION['reservation']['itinerary'])) {
            $_SESSION['reservation']['step'] = 1;
        }
    }
} else if (isset($_GET['step'])) {
    $_SESSION['reservation']['step'] = $_GET['step'];
} else {
    $_SESSION['reservation']['step'] = 1;
}

$reservation = $_SESSION['reservation'];

if (isset($reservation['itinerary'])) {
    $itinerary = $reservation['itinerary'];
    $pick_up_val = $itinerary['pickUpDate']['value'];
    $return_val = $itinerary['returnDate']['value'];
    $pick_up_step_val = "{$itinerary['pickUpLocation']} - {$itinerary['pickUpDate']['altValue']}";
    $return_step_val = "{$itinerary['returnLocation']} - {$itinerary['returnDate']['altValue']}";
    $pick_up_location = $itinerary['pickUpLocation'];
    $return_location = $itinerary['returnLocation'];
} else {
    $pick_up_step_val = "--";
    $return_step_val = "--";
    $pick_up_val = "";
    $return_val = "";
}

if (isset($reservation['vehicle'])) {
    $vehicle = $reservation['vehicle'];
    $vehicle_name = $vehicle['name'];
    $vehicle_type = $vehicle['type'];
    $vehicle_img_src = $vehicle['imgSrc'];
} else {
    $vehicle_name = "Type";
    $vehicle_type = "--";
}

$session_add_ons = [];
if (isset($reservation['add_ons']) && count($reservation['add_ons']) > 0) {
    $session_add_ons = $reservation['add_ons'];
}
$session_add_ons = count($session_add_ons) ? $session_add_ons : [
    [
        "id" => "",
        "abbr" => "--"
    ]
];

if ($testing) {
    // echo "<pre>";
    // print_r($_SESSION);
    // echo "</pre>";
}

?>

<section class="general-header">
    <h1>Reservation</h1>
</section>

<section id="reservation-steps">
    <div class="inner">
        <div class="reservation-step itinerary <?php echo $reservation['step'] == 1 && !$see_all_vehicles ? "active" : ""; ?>" data-step="1">
            <div class="header">
                <span>1</span>
                <h2>Your Itinerary</h2>
            </div>
            <div class="body">
                <div>
                    <h6>Pick Up</h6>
                    <p><?php echo $pick_up_step_val; ?></p>
                </div>
                <div>
                    <h6>Drop Off</h6>
                    <p><?php echo $return_step_val; ?></p>
                </div>
            </div>
        </div>
        <div class="reservation-step vehicle-add-on <?php echo $reservation['step'] == 2 || $see_all_vehicles ? "active" : ""; ?>" data-step="2">
            <div class="header">
                <span>2</span>
                <h2>Select Vehicle/Add-ons</h2>
            </div>
            <div class="body">
                <div>
                    <h6><?php echo $vehicle_name; ?></h6>
                    <p><?php echo $vehicle_type; ?></p>
                </div>
                <div>
                    <h6>Add-ons</h6>
                    <p>
                        <?php
                        $counter = 0;
                        foreach ($session_add_ons as $add_on) {
                            $prefix = $counter > 0 ? ", " : "";
                            $counter++;
                            echo "<span data-id=\"{$add_on['id']}\">{$prefix}{$add_on['abbr']}</span>";
                        } ?>
                    </p>
                </div>
            </div>
        </div>
        <div class="reservation-step reservation <?php echo $reservation['step'] == 3 && !$see_all_vehicles ? "active" : ""; ?>" data-step="3">
            <div class="header">
                <span>3</span>
                <h2>Reserve Your Vehicle</h2>
            </div>
            <div class="body">
                <div>
                    <h6>Your Information</h6>
                    <p>--</p>
                </div>
                <div>
                    <h6>Payment Information</h6>
                    <p>--</p>
                </div>
            </div>
        </div>
    </div>
</section>

<section class="booking-flow-section" id="itinerary-section" data-step="1" <?php if ($see_all_vehicles || $reservation['step'] != 1) echo 'style="display:none;"'; ?>>
    <div class="inner">
        <h2>Reserve Your Vehicle</h2>
        <div class="reservation-flow-container">
            <div class="left">
                <div>
                    <h2>Pick Up</h2>
                    <div class="main-itinerary-box">
                        <div>
                            <h6>Place to pick up the Car<sup>*</sup></h6>
                            <div class="custom-select pick-up form-input">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" />
                                </svg>
                                <span><?php echo isset($pick_up_location) ? $pick_up_location : "Choose Office"; ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z" />
                                </svg>
                                <div class="custom-select-options">
                                    <span <?php echo (isset($pick_up_location) && $pick_up_location === "Choose Office") ? 'class="selected"' : "" ?>>Choose Office</span>
                                    <span <?php echo (isset($pick_up_location) && $pick_up_location === "Airport") ? 'class="selected"' : "" ?>>Airport</span>
                                </div>
                            </div>
                        </div>
                        <div class="checkbox-container">
                            <?php if (isset($itinerary)) { ?>
                                <input id="return-to-same-location" type="checkbox" class="hidden-checkbox" hidden <?php if ($itinerary['returnToSameLocation']['value'] === 'on') echo 'checked aria-checked="true"'; ?> />
                            <?php } else { ?>
                                <input id="return-to-same-location" type="checkbox" class="hidden-checkbox" hidden checked aria-checked="true" />
                            <?php } ?>
                            <div class="custom-checkbox"></div>
                            <label class="custom-checkbox-label">Return to the same location</label>
                        </div>
                        <div>
                            <h6>Pick-up Date/Time<sup>*</sup></h6>
                            <input type="text" id="pick-up-flatpickr" class="form-input flatpickr-input" placeholder="Pickup Date" value="<?php echo $pick_up_val; ?>">
                        </div>
                    </div>
                    <h2>Return</h2>
                    <div class="main-itinerary-box">
                        <div <?php if (!isset($itinerary) || $itinerary['returnToSameLocation']['value'] === 'on') echo 'style="display: none;"'; ?>>
                            <h6>Place to drop the Car<sup>*</sup></h6>
                            <div class="custom-select return form-input">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" />
                                </svg>
                                <span><?php echo isset($return_location) ? $return_location : "Choose Office"; ?></span>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z" />
                                </svg>
                                <div class="custom-select-options">
                                    <span <?php echo isset($return_location) && $return_location === "Choose Office" ? 'class="selected"' : "" ?>>Choose Office</span>
                                    <span <?php echo isset($return_location) && $return_location === "Airport" ? 'class="selected"' : "" ?>>Airport</span>
                                </div>
                            </div>
                        </div>
                        <div>
                            <h6>Drop Date/Time<sup>*</sup></h6>
                            <input type="text" id="return-flatpickr" class="form-input flatpickr-input" placeholder="Return Date" value="<?php echo $return_val; ?>">
                        </div>
                    </div>
                    <div class="continue-btn">Continue Reservation</div>
                    <?php if (isset($itinerary)) { ?>
                        <div class="reset-data">
                            <span>reset data</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-refresh-cw" stroke-width="3" width="24" height="24" viewBox="0 0 24 24">
                                <path d="M3 12a9 9 0 0 1 9-9 9.75 9.75 0 0 1 6.74 2.74L21 8"></path>
                                <path d="M21 3v5h-5"></path>
                                <path d="M21 12a9 9 0 0 1-9 9 9.75 9.75 0 0 1-6.74-2.74L3 16"></path>
                                <path d="M8 16H3v5"></path>
                            </svg>
                        </div>
                    <?php } ?>
                </div>
            </div>
            <?php include '../includes/reservation-summary.php'; ?>
        </div>
    </div>
</section>

<section class="booking-flow-section" id="vehicle-selection-section" data-step="2" <?php echo $see_all_vehicles ? "" : ($reservation['step'] != 2 ? 'style="display:none;"' : (isset($vehicle) ? 'style="display:none;"' : "")); ?>>
    <div class="inner">
        <h2>Select Vehicle</h2>
        <div id="vehicles">
            <?php foreach ($vehicles_arr as $v) {
                $active_vehicle = isset($vehicle) && ($vehicle['id'] == $v['id']);
            ?>
                <div class="vehicle-container <?php echo $active_vehicle ? "active" : ""; ?>" data-vehicle-id="<?php echo $v['id']; ?>">
                    <img src="/assets/images/vehicles/<?php echo $v['slug']; ?>.avif" alt="<?php echo $v['name']; ?> thumbnail">
                    <div class="center">
                        <div>
                            <span class="vehicle-name"><?php echo $v['name']; ?></span>
                            <span class="vehicle-type"><?php echo $v['type']; ?></span>
                        </div>
                        <div>
                            <div>
                                <svg height="800px" width="800px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 240.235 240.235" xml:space="preserve">
                                    <path d="M211.744,6.089C208.081,2.163,203.03,0,197.52,0h-15.143c-11.16,0-21.811,8.942-23.74,19.934l-0.955,5.436  c-0.96,5.47,0.332,10.651,3.639,14.589c3.307,3.938,8.186,6.106,13.74,6.106h19.561c2.714,0,5.339-0.542,7.778-1.504l-2.079,17.761  c-2.001-0.841-4.198-1.289-6.507-1.289h-22.318c-9.561,0-18.952,7.609-20.936,16.961l-19.732,93.027l-93.099-6.69  c-5.031-0.36-9.231,1.345-11.835,4.693c-2.439,3.136-3.152,7.343-2.009,11.847l10.824,42.618  c2.345,9.233,12.004,16.746,21.53,16.746h78.049h1.191h39.729c9.653,0,18.336-7.811,19.354-17.411l15.272-143.981  c0.087-0.823,0.097-1.634,0.069-2.437l5.227-44.648c0.738-1.923,1.207-3.967,1.354-6.087l0.346-4.97  C217.214,15.205,215.407,10.016,211.744,6.089z" />
                                </svg>
                                <span><?php echo $v['people']; ?> Seats</span>
                            </div>
                            <?php if ($v['bags'] > 0) { ?>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                        <path d="M184 48l144 0c4.4 0 8 3.6 8 8l0 40L176 96l0-40c0-4.4 3.6-8 8-8zm-56 8l0 40L64 96C28.7 96 0 124.7 0 160l0 96 192 0 128 0 192 0 0-96c0-35.3-28.7-64-64-64l-64 0 0-40c0-30.9-25.1-56-56-56L184 0c-30.9 0-56 25.1-56 56zM512 288l-192 0 0 32c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-32L0 288 0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-128z" />
                                    </svg>
                                    <span><?php echo $v['bags']; ?> Bags</span>
                                </div>
                            <?php } ?>
                            <div>
                                <svg width="800px" height="800px" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <defs>
                                        <mask id="doorMask">
                                            <rect width="100%" height="100%" fill="white" />
                                            <path d="M18,14H15a1,1,0,0,1,0-2h3a1,1,0,0,1,0,2Z" fill="black" />
                                        </mask>
                                    </defs>
                                    <path d="M19,2H12.41A2,2,0,0,0,11,2.59l-7.71,7.7A1,1,0,0,0,3,11v4.13a2,2,0,0,0,1.72,2l2.06.3A5.11,5.11,0,0,1,11,21.24,1,1,0,0,0,12,22h7a2,2,0,0,0,2-2V4A2,2,0,0,0,19,2Zm0,8H6.41l6-6H19Z" mask="url(#doorMask)"></path>
                                </svg>
                                <span><?php echo $v['doors']; ?> Doors</span>
                            </div>
                            <?php if ($v['4wd']) { ?>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 -960 960 960" width="40" stroke-width="24" class="add-stroke">
                                        <path d="M155.667-150.667q-42.417 0-71.375-29.458-28.958-29.459-28.958-71.542 0-36.105 23-63.386 23-27.28 57-33.947v-262q-34-6.667-57-33.947-23-27.281-23-63.915 0-41.863 28.958-71.167 28.958-29.304 71.375-29.304t71.708 29.304q29.292 29.304 29.292 71.167 0 36.634-23.334 63.915Q210-617.667 176.667-611v110h283v-110q-34-6.667-57-33.947-23-27.281-23-63.915 0-41.863 28.958-71.167 28.958-29.304 71.375-29.304t71.708 29.304Q581-750.725 581-708.862q0 36.634-23.333 63.915Q534.333-617.667 501-611v110h225.333q24.675 0 41.838-17.096 17.162-17.096 17.162-41.904v-51.356q-34-6.311-57-33.591-23-27.281-23-63.915 0-41.863 29.39-71.167 29.39-29.304 71.375-29.304t71.277 29.304q29.291 29.304 29.291 71.167 0 36.634-23 63.915-23 27.28-57 33.591V-560q0 42.583-29.264 71.458-29.263 28.875-71.069 28.875H501V-349q33.333 6.667 56.667 33.947Q581-287.772 581-251.667q0 42.083-29.458 71.542-29.459 29.458-71.542 29.458-42.417 0-71.375-29.458-28.958-29.459-28.958-71.542 0-36.105 23-63.386 23-27.28 57-33.947v-110.667h-283V-349q33.333 6.667 56.666 33.947 23.334 27.281 23.334 63.386 0 42.083-29.459 71.542-29.458 29.458-71.541 29.458Zm0-41.333q24.808 0 42.238-17.012 17.429-17.013 17.429-42.321 0-24.476-17.324-41.905-17.324-17.429-42.238-17.429-24.914 0-42.009 17.579-17.096 17.579-17.096 41.755 0 25.141 17.162 42.237Q130.992-192 155.667-192Zm0-457.333q24.808 0 42.238-16.979 17.429-16.979 17.429-42.238 0-25.258-17.324-42.354T155.772-768q-24.914 0-42.009 17.162Q96.667-733.675 96.667-709q0 24.809 17.162 42.238 17.163 17.429 41.838 17.429ZM480-192q24.808 0 42.238-17.012 17.429-17.013 17.429-42.321 0-24.476-17.429-41.905-17.43-17.429-42.238-17.429t-41.904 17.579Q421-275.509 421-251.333q0 25.141 17.096 42.237Q455.192-192 480-192Zm0-457.333q24.808 0 42.238-16.979 17.429-16.979 17.429-42.238 0-25.258-17.429-42.354Q504.808-768 480-768t-41.904 17.129Q421-733.742 421-709.117q0 25.592 17.096 42.688 17.096 17.096 41.904 17.096Zm326.333 0q24.808 0 41.904-16.979 17.096-16.979 17.096-42.238 0-25.258-17.162-42.354Q831.008-768 806.333-768q-24.808 0-42.238 17.129-17.429 17.129-17.429 41.754 0 25.592 17.429 42.688 17.43 17.096 42.238 17.096ZM155.667-251.667Zm0-457.333ZM480-251.667ZM480-709Zm326.333 0Z" />
                                    </svg>
                                    <span>4WD</span>
                                </div>
                            <?php } ?>
                            <?php if ($v['ac']) { ?>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                        <path d="M224 0c13.3 0 24 10.7 24 24V70.1l23-23c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-57 57v76.5l66.2-38.2 20.9-77.8c3.4-12.8 16.6-20.4 29.4-17s20.4 16.6 17 29.4L373 142.2l37.1-21.4c11.5-6.6 26.2-2.7 32.8 8.8s2.7 26.2-8.8 32.8L397 183.8l31.5 8.4c12.8 3.4 20.4 16.6 17 29.4s-16.6 20.4-29.4 17l-77.8-20.9L272 256l66.2 38.2 77.8-20.9c12.8-3.4 26 4.2 29.4 17s-4.2 26-17 29.4L397 328.2l37.1 21.4c11.5 6.6 15.4 21.3 8.8 32.8s-21.3 15.4-32.8 8.8L373 369.8l8.4 31.5c3.4 12.8-4.2 26-17 29.4s-26-4.2-29.4-17l-20.9-77.8L248 297.6v76.5l57 57c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-23-23V488c0 13.3-10.7 24-24 24s-24-10.7-24-24V441.9l-23 23c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l57-57V297.6l-66.2 38.2-20.9 77.8c-3.4 12.8-16.6 20.4-29.4 17s-20.4-16.6-17-29.4L75 369.8 37.9 391.2c-11.5 6.6-26.2 2.7-32.8-8.8s-2.7-26.2 8.8-32.8L51 328.2l-31.5-8.4c-12.8-3.4-20.4-16.6-17-29.4s16.6-20.4 29.4-17l77.8 20.9L176 256l-66.2-38.2L31.9 238.6c-12.8 3.4-26-4.2-29.4-17s4.2-26 17-29.4L51 183.8 13.9 162.4c-11.5-6.6-15.4-21.3-8.8-32.8s21.3-15.4 32.8-8.8L75 142.2l-8.4-31.5c-3.4-12.8 4.2-26 17-29.4s26 4.2 29.4 17l20.9 77.8L200 214.4V137.9L143 81c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l23 23V24c0-13.3 10.7-24 24-24z" />
                                    </svg>
                                    <span>A/C</span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="right">
                        <div>
                            <span>USD$<?php echo $v['base_price_USD']; ?></span>
                            <span>/Day</span>
                        </div>
                        <div class="continue-btn"><?php echo $active_vehicle ? "CONTINUE" : "BOOK NOW"; ?></div>
                    </div>
                </div>
            <?php } ?>
        </div>
    </div>
</section>

<section class="booking-flow-section" id="vehicle-add-ons" data-step="2" <?php echo $see_all_vehicles || $reservation['step'] != 2 ? 'style="display:none;"' : (isset($vehicle) ? "" : 'style="display:none;"'); ?>>
    <div class="inner">
        <h2>Vehicle Add-ons</h2>
        <div class="reservation-flow-container">
            <div class="left">
                <div id="add-ons">
                    <?php foreach ($add_ons_arr as $add_on) { ?>
                        <div class="add-on-container" data-id="<?php echo $add_on['id']; ?>" data-add-on-name="<?php echo $add_on['name']; ?>">
                            <div class="top">
                                <div class="left">
                                    <?php
                                    $add_on_text = $add_on['name'];
                                    if ($add_on['name'] === "Collision Insurance" && isset($vehicle)) {
                                        $add_on_text .= " - \${$vehicle['insurance']}/day";
                                    }
                                    ?>
                                    <h2><?php echo $add_on_text; ?></h2>
                                    <div class="more-add-on-info">
                                        <span>More Information</span>
                                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-down">
                                            <path d="m6 9 6 6 6-6" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="add-on-btn <?php echo isset($session_add_ons[$add_on['id']]) ? "added" : ""; ?>"></div>
                            </div>
                            <p><?php echo makeAddOnDescriptionStr($add_on, $vehicles_arr); ?></p>
                        </div>
                    <?php } ?>
                </div>
                <div class="continue-btn">Continue Reservation</div>
            </div>
            <?php include '../includes/reservation-summary.php'; ?>
        </div>
    </div>
</section>

<section class="booking-flow-section" id="final-section" data-step="3" <?php echo $see_all_vehicles || $reservation['step'] != 3 ? 'style="display:none;"' : ""; ?>>
    <div class="inner">
        <h2>Final Details</h2>
        <div class="reservation-flow-container">
            <div class="left">
                <div id="final-details-form">
                    <h2>Contact Info</h2>
                    <div class="input-container">
                        <h6>Hotel in Antigua (optional)</h6>
                        <input type="text" name="hotel">
                    </div>
                    <div class="mutiple-input-container">
                        <div class="input-container">
                            <h6>First Name<sup>*</sup></h6>
                            <input class="form-input" type="text" name="first-name">
                        </div>
                        <div class="input-container">
                            <h6>Last Name<sup>*</sup></h6>
                            <input class="form-input" type="text" name="last-name">
                        </div>
                    </div>
                    <div class="mutiple-input-container">
                        <div class="input-container">
                            <h6>Driver's License (Optional)</h6>
                            <input type="text" name="driver-license">
                        </div>
                        <div class="input-container">
                            <h6>Country / Region<sup>*</sup></h6>
                            <input class="form-input" type="text" name="country-region">
                        </div>
                    </div>
                    <div class="input-container">
                        <h6>Street address<sup>*</sup></h6>
                        <input class="form-input" type="text" name="street">
                    </div>
                    <div class="input-container">
                        <h6>Town / City<sup>*</sup></h6>
                        <input class="form-input" type="text" name="town-city">
                    </div>
                    <div class="input-container">
                        <h6>State / County (optional)</h6>
                        <input type="text" name="state-county">
                    </div>
                    <div class="input-container">
                        <h6>Phone <sup>*</sup></h6>
                        <input class="form-input" type="text" name="phone">
                    </div>
                    <div class="input-container">
                        <h6>Email address <sup>*</sup></h6>
                        <input class="form-input" type="text" name="email">
                    </div>
                    <div class="continue-btn">Send Request</div>
                </div>
            </div>
            <?php include '../includes/reservation-summary.php'; ?>
        </div>
</section>


<?php include_once '../includes/footer.php'; ?>