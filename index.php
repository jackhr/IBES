<?php

include_once 'includes/env.php';

$title_override = "Affordable Antigua Car Rental for Your Perfect Island Adventure";
$page = "index";
$description = "$company_name offers affordable, well-maintained vehicles. Enjoy online booking and exceptional customer service. Rent a car in Antigua today!";
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
            "logo" => "https://$www_domain/assets/images/logo.jpeg"
        ]
    ],
    [
        "@context" => "https://schema.org",
        "@type" => "LocalBusiness",
        "name" => $company_name,
        "description" => "Rent affordable and well-maintained cars in Antigua and Barbuda.",
        "image" => "https://$www_domain/logo.jpeg",
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

include_once 'includes/header.php';

$vehicles_arr = [];

$query = "SELECT * FROM vehicles WHERE landing_order IS NOT NULL ORDER BY landing_order ASC";
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) $vehicles_arr[$row['id']] = $row;

$query = "SELECT * FROM vehicle_discounts";
$result = mysqli_query($con, $query);
while ($row = mysqli_fetch_assoc($result)) {
    if ($vehicles_arr[$row['vehicle_id']]) {
        $vehicles_arr[$row['vehicle_id']]['discount_days'] = $row['days'];
    };
}

?>

<section id="intro-section">

    <div class="inner">

        <img src="/assets/images/logo.jpeg" alt="Website logo">

        <form action="">

            <h2>PICK UP</h2>
            <div class="custom-select pick-up form-input">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" />
                </svg>
                <span>Choose Location</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z" />
                </svg>
                <div class="custom-select-options">
                    <span class="selected">Choose Location</span>
                    <span>Airport</span>
                    <span>Your Hotel</span>
                </div>
            </div>

            <div class="checkbox-container">
                <input id="return-to-same-location" type="checkbox" class="hidden-checkbox" hidden checked aria-checked="true" />
                <div class="custom-checkbox"></div>
                <label class="custom-checkbox-label">Return to the same location</label>
            </div>

            <div>
                <input type="text" id="pick-up-flatpickr" class="flatpickr-input form-input" placeholder="Pickup Date">
            </div>

            <h2>RETURN</h2>
            <div class="custom-select return form-input" style="display: none;">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 384 512"><!--Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path d="M215.7 499.2C267 435 384 279.4 384 192C384 86 298 0 192 0S0 86 0 192c0 87.4 117 243 168.3 307.2c12.3 15.3 35.1 15.3 47.4 0zM192 128a64 64 0 1 1 0 128 64 64 0 1 1 0-128z" />
                </svg>
                <span>Choose Location</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path d="M233.4 406.6c12.5 12.5 32.8 12.5 45.3 0l192-192c12.5-12.5 12.5-32.8 0-45.3s-32.8-12.5-45.3 0L256 338.7 86.6 169.4c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3l192 192z" />
                </svg>
                <div class="custom-select-options">
                    <span class="selected">Choose Location</span>
                    <span>Airport</span>
                    <span>Your Hotel</span>
                </div>
            </div>
            <div>
                <input type="text" id="return-flatpickr" class="flatpickr-input form-input" placeholder="Return Date">
            </div>

            <button type="submit">
                <span>Find a Vehicle</span>
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                    <path d="M438.6 278.6c12.5-12.5 12.5-32.8 0-45.3l-160-160c-12.5-12.5-32.8-12.5-45.3 0s-12.5 32.8 0 45.3L338.8 224 32 224c-17.7 0-32 14.3-32 32s14.3 32 32 32l306.7 0L233.4 393.4c-12.5 12.5-12.5 32.8 0 45.3s32.8 12.5 45.3 0l160-160z" />
                </svg>
            </button>

        </form>

    </div>

</section>

<section id="feature-section">

    <div class="inner">
        <h1>Antigua Car Rental Services</h1>
        <div id="features">
            <div class="feature-container">
                <div class="feature-icon">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path d="M4.1 38.2C1.4 34.2 0 29.4 0 24.6C0 11 11 0 24.6 0H133.9c11.2 0 21.7 5.9 27.4 15.5l68.5 114.1c-48.2 6.1-91.3 28.6-123.4 61.9L4.1 38.2zm503.7 0L405.6 191.5c-32.1-33.3-75.2-55.8-123.4-61.9L350.7 15.5C356.5 5.9 366.9 0 378.1 0H487.4C501 0 512 11 512 24.6c0 4.8-1.4 9.6-4.1 13.6zM80 336a176 176 0 1 1 352 0A176 176 0 1 1 80 336zm184.4-94.9c-3.4-7-13.3-7-16.8 0l-22.4 45.4c-1.4 2.8-4 4.7-7 5.1L168 298.9c-7.7 1.1-10.7 10.5-5.2 16l36.3 35.4c2.2 2.2 3.2 5.2 2.7 8.3l-8.6 49.9c-1.3 7.6 6.7 13.5 13.6 9.9l44.8-23.6c2.7-1.4 6-1.4 8.7 0l44.8 23.6c6.9 3.6 14.9-2.2 13.6-9.9l-8.6-49.9c-.5-3 .5-6.1 2.7-8.3l36.3-35.4c5.6-5.4 2.5-14.8-5.2-16l-50.1-7.3c-3-.4-5.7-2.4-7-5.1l-22.4-45.4z" />
                    </svg>
                </div>
                <div class="feature-info">
                    <h2>Quality Vehicles</h2>
                    <p>Each day, we prioritize our clients' well-being above all else. Ensuring safety, cleanliness, and reliability remains our foremost commitment. Our fleet undergoes rigorous maintenance, meeting the highest safety benchmarks for your peace of mind.</p>
                </div>
            </div>
            <div class="feature-container">
                <div class="feature-icon">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path d="M135.2 117.4L109.1 192H402.9l-26.1-74.6C372.3 104.6 360.2 96 346.6 96H165.4c-13.6 0-25.7 8.6-30.2 21.4zM39.6 196.8L74.8 96.3C88.3 57.8 124.6 32 165.4 32H346.6c40.8 0 77.1 25.8 90.6 64.3l35.2 100.5c23.2 9.6 39.6 32.5 39.6 59.2V400v48c0 17.7-14.3 32-32 32H448c-17.7 0-32-14.3-32-32V400H96v48c0 17.7-14.3 32-32 32H32c-17.7 0-32-14.3-32-32V400 256c0-26.7 16.4-49.6 39.6-59.2zM128 288a32 32 0 1 0 -64 0 32 32 0 1 0 64 0zm288 32a32 32 0 1 0 0-64 32 32 0 1 0 0 64z" />
                    </svg>
                </div>
                <div class="feature-info">
                    <h2>Driving In Antigua</h2>
                    <p>Welcome to our enchanting island nation! Our aim is to enrich your journey as you explore and uncover the beauty of Antigua. As a unique characteristic, we drive on the left, adhering to international driving norms and regulations to ensure a seamless and safe experience for all travelers.
                    </p>
                </div>
            </div>
            <div class="feature-container">
                <div class="feature-icon">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path d="M323.8 34.8c-38.2-10.9-78.1 11.2-89 49.4l-5.7 20c-3.7 13-10.4 25-19.5 35l-51.3 56.4c-8.9 9.8-8.2 25 1.6 33.9s25 8.2 33.9-1.6l51.3-56.4c14.1-15.5 24.4-34 30.1-54.1l5.7-20c3.6-12.7 16.9-20.1 29.7-16.5s20.1 16.9 16.5 29.7l-5.7 20c-5.7 19.9-14.7 38.7-26.6 55.5c-5.2 7.3-5.8 16.9-1.7 24.9s12.3 13 21.3 13L448 224c8.8 0 16 7.2 16 16c0 6.8-4.3 12.7-10.4 15c-7.4 2.8-13 9-14.9 16.7s.1 15.8 5.3 21.7c2.5 2.8 4 6.5 4 10.6c0 7.8-5.6 14.3-13 15.7c-8.2 1.6-15.1 7.3-18 15.2s-1.6 16.7 3.6 23.3c2.1 2.7 3.4 6.1 3.4 9.9c0 6.7-4.2 12.6-10.2 14.9c-11.5 4.5-17.7 16.9-14.4 28.8c.4 1.3 .6 2.8 .6 4.3c0 8.8-7.2 16-16 16H286.5c-12.6 0-25-3.7-35.5-10.7l-61.7-41.1c-11-7.4-25.9-4.4-33.3 6.7s-4.4 25.9 6.7 33.3l61.7 41.1c18.4 12.3 40 18.8 62.1 18.8H384c34.7 0 62.9-27.6 64-62c14.6-11.7 24-29.7 24-50c0-4.5-.5-8.8-1.3-13c15.4-11.7 25.3-30.2 25.3-51c0-6.5-1-12.8-2.8-18.7C504.8 273.7 512 257.7 512 240c0-35.3-28.6-64-64-64l-92.3 0c4.7-10.4 8.7-21.2 11.8-32.2l5.7-20c10.9-38.2-11.2-78.1-49.4-89zM32 192c-17.7 0-32 14.3-32 32V448c0 17.7 14.3 32 32 32H96c17.7 0 32-14.3 32-32V224c0-17.7-14.3-32-32-32H32z" />
                    </svg>
                </div>
                <div class="feature-info">
                    <h2>Outstanding Service</h2>
                    <p>At <?php echo $company_name; ?>, we're dedicated to delivering a service experience that surpasses expectations. Our approach is rooted in honesty, professionalism, and a warm, friendly demeanor towards all our clients. We prioritize your user experience, understanding that the most powerful endorsement comes from satisfied customers sharing their positive experiences through word-of-mouth.</p>
                </div>
            </div>
            <div class="feature-container">
                <div class="feature-icon">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path d="M429.6 92.1c4.9-11.9 2.1-25.6-7-34.7s-22.8-11.9-34.7-7l-352 144c-14.2 5.8-22.2 20.8-19.3 35.8s16.1 25.8 31.4 25.8H224V432c0 15.3 10.8 28.4 25.8 31.4s30-5.1 35.8-19.3l144-352z" />
                    </svg>
                </div>
                <div class="feature-info">
                    <h2>Add-On Options</h2>
                    <p>We strive to enhance your journey by offering complimentary child safety seats and GPS turn-by-turn navigation whenever possible. If you're interested, simply inform us during the booking process. Additionally, maps of Antigua are readily available in every vehicle for your convenience. It's worth noting that according to the law, child seats are recommended for children under the age of four.</p>
                </div>
            </div>
            <div class="feature-container">
                <div class="feature-icon">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path d="M280 0C408.1 0 512 103.9 512 232c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-101.6-82.4-184-184-184c-13.3 0-24-10.7-24-24s10.7-24 24-24zm8 192a32 32 0 1 1 0 64 32 32 0 1 1 0-64zm-32-72c0-13.3 10.7-24 24-24c75.1 0 136 60.9 136 136c0 13.3-10.7 24-24 24s-24-10.7-24-24c0-48.6-39.4-88-88-88c-13.3 0-24-10.7-24-24zM117.5 1.4c19.4-5.3 39.7 4.6 47.4 23.2l40 96c6.8 16.3 2.1 35.2-11.6 46.3L144 207.3c33.3 70.4 90.3 127.4 160.7 160.7L345 318.7c11.2-13.7 30-18.4 46.3-11.6l96 40c18.6 7.7 28.5 28 23.2 47.4l-24 88C481.8 499.9 466 512 448 512C200.6 512 0 311.4 0 64C0 46 12.1 30.2 29.5 25.4l88-24z" />
                    </svg>
                </div>
                <div class="feature-info">
                    <h2>24 Hour Support</h2>
                    <p>In our ongoing commitment to exceptional service, we offer round-the-clock support to assist you whenever necessary. While roads and signage might not always be the most straightforward, our intimate knowledge of Antigua ensures that we can promptly reach all our customers in times of need.</p>
                </div>
            </div>
            <div class="feature-container">
                <div class="feature-icon">
                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                        <path d="M512 80c8.8 0 16 7.2 16 16v32H48V96c0-8.8 7.2-16 16-16H512zm16 144V416c0 8.8-7.2 16-16 16H64c-8.8 0-16-7.2-16-16V224H528zM64 32C28.7 32 0 60.7 0 96V416c0 35.3 28.7 64 64 64H512c35.3 0 64-28.7 64-64V96c0-35.3-28.7-64-64-64H64zm56 304c-13.3 0-24 10.7-24 24s10.7 24 24 24h48c13.3 0 24-10.7 24-24s-10.7-24-24-24H120zm128 0c-13.3 0-24 10.7-24 24s10.7 24 24 24H360c13.3 0 24-10.7 24-24s-10.7-24-24-24H248z" />
                    </svg>
                </div>
                <div class="feature-info">
                    <h2>Payment</h2>
                    <p>We gladly accept major credit cards, including Visa, MasterCard, and American Express.<br><br>For discounted rates, simply book for two or more days.</p>
                </div>
            </div>
        </div>
    </div>

</section>

<section id="landing-cars-section">
    <div class="inner">
        <div id="cars">
            <?php foreach ($vehicles_arr as $vehicle) { ?>
                <a class="car-container" href="/reservation/?vehicle_id=<?php echo $vehicle['id']; ?>">
                    <div class="overlay">
                        <div></div>
                    </div>
                    <div class="top">
                        <div class="left">
                            <h2><?php echo $vehicle['name']; ?></h2>
                            <h3><?php echo "{$vehicle['type']} - USD\${$vehicle['insurance']}/day Insurance"; ?></h3>
                            <div>
                                <span>FROM</span>
                                <span>USD$<?php echo $vehicle['base_price_USD']; ?><span style="font-size: 15px;">/</span></span>
                                <span>DAY</span>
                            </div>
                        </div>
                        <div class="right">
                            <div>
                                <svg height="800px" width="800px" version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 240.235 240.235" xml:space="preserve">
                                    <path d="M211.744,6.089C208.081,2.163,203.03,0,197.52,0h-15.143c-11.16,0-21.811,8.942-23.74,19.934l-0.955,5.436  c-0.96,5.47,0.332,10.651,3.639,14.589c3.307,3.938,8.186,6.106,13.74,6.106h19.561c2.714,0,5.339-0.542,7.778-1.504l-2.079,17.761  c-2.001-0.841-4.198-1.289-6.507-1.289h-22.318c-9.561,0-18.952,7.609-20.936,16.961l-19.732,93.027l-93.099-6.69  c-5.031-0.36-9.231,1.345-11.835,4.693c-2.439,3.136-3.152,7.343-2.009,11.847l10.824,42.618  c2.345,9.233,12.004,16.746,21.53,16.746h78.049h1.191h39.729c9.653,0,18.336-7.811,19.354-17.411l15.272-143.981  c0.087-0.823,0.097-1.634,0.069-2.437l5.227-44.648c0.738-1.923,1.207-3.967,1.354-6.087l0.346-4.97  C217.214,15.205,215.407,10.016,211.744,6.089z" />
                                </svg>
                                <span><?php echo $vehicle['people']; ?> Seats</span>
                            </div>
                            <?php if ($vehicle['bags'] > 0) { ?>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512"><!--!Font Awesome Free 6.6.0 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                        <path d="M184 48l144 0c4.4 0 8 3.6 8 8l0 40L176 96l0-40c0-4.4 3.6-8 8-8zm-56 8l0 40L64 96C28.7 96 0 124.7 0 160l0 96 192 0 128 0 192 0 0-96c0-35.3-28.7-64-64-64l-64 0 0-40c0-30.9-25.1-56-56-56L184 0c-30.9 0-56 25.1-56 56zM512 288l-192 0 0 32c0 17.7-14.3 32-32 32l-64 0c-17.7 0-32-14.3-32-32l0-32L0 288 0 416c0 35.3 28.7 64 64 64l384 0c35.3 0 64-28.7 64-64l0-128z" />
                                    </svg>
                                    <span><?php echo $vehicle['bags']; ?> Bags</span>
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
                                <span><?php echo $vehicle['doors']; ?> Doors</span>
                            </div>
                            <?php if ($vehicle['4wd']) { ?>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" height="40" viewBox="0 -960 960 960" width="40" stroke-width="24" class="add-stroke">
                                        <path d="M155.667-150.667q-42.417 0-71.375-29.458-28.958-29.459-28.958-71.542 0-36.105 23-63.386 23-27.28 57-33.947v-262q-34-6.667-57-33.947-23-27.281-23-63.915 0-41.863 28.958-71.167 28.958-29.304 71.375-29.304t71.708 29.304q29.292 29.304 29.292 71.167 0 36.634-23.334 63.915Q210-617.667 176.667-611v110h283v-110q-34-6.667-57-33.947-23-27.281-23-63.915 0-41.863 28.958-71.167 28.958-29.304 71.375-29.304t71.708 29.304Q581-750.725 581-708.862q0 36.634-23.333 63.915Q534.333-617.667 501-611v110h225.333q24.675 0 41.838-17.096 17.162-17.096 17.162-41.904v-51.356q-34-6.311-57-33.591-23-27.281-23-63.915 0-41.863 29.39-71.167 29.39-29.304 71.375-29.304t71.277 29.304q29.291 29.304 29.291 71.167 0 36.634-23 63.915-23 27.28-57 33.591V-560q0 42.583-29.264 71.458-29.263 28.875-71.069 28.875H501V-349q33.333 6.667 56.667 33.947Q581-287.772 581-251.667q0 42.083-29.458 71.542-29.459 29.458-71.542 29.458-42.417 0-71.375-29.458-28.958-29.459-28.958-71.542 0-36.105 23-63.386 23-27.28 57-33.947v-110.667h-283V-349q33.333 6.667 56.666 33.947 23.334 27.281 23.334 63.386 0 42.083-29.459 71.542-29.458 29.458-71.541 29.458Zm0-41.333q24.808 0 42.238-17.012 17.429-17.013 17.429-42.321 0-24.476-17.324-41.905-17.324-17.429-42.238-17.429-24.914 0-42.009 17.579-17.096 17.579-17.096 41.755 0 25.141 17.162 42.237Q130.992-192 155.667-192Zm0-457.333q24.808 0 42.238-16.979 17.429-16.979 17.429-42.238 0-25.258-17.324-42.354T155.772-768q-24.914 0-42.009 17.162Q96.667-733.675 96.667-709q0 24.809 17.162 42.238 17.163 17.429 41.838 17.429ZM480-192q24.808 0 42.238-17.012 17.429-17.013 17.429-42.321 0-24.476-17.429-41.905-17.43-17.429-42.238-17.429t-41.904 17.579Q421-275.509 421-251.333q0 25.141 17.096 42.237Q455.192-192 480-192Zm0-457.333q24.808 0 42.238-16.979 17.429-16.979 17.429-42.238 0-25.258-17.429-42.354Q504.808-768 480-768t-41.904 17.129Q421-733.742 421-709.117q0 25.592 17.096 42.688 17.096 17.096 41.904 17.096Zm326.333 0q24.808 0 41.904-16.979 17.096-16.979 17.096-42.238 0-25.258-17.162-42.354Q831.008-768 806.333-768q-24.808 0-42.238 17.129-17.429 17.129-17.429 41.754 0 25.592 17.429 42.688 17.43 17.096 42.238 17.096ZM155.667-251.667Zm0-457.333ZM480-251.667ZM480-709Zm326.333 0Z" />
                                    </svg>
                                    <span>4WD</span>
                                </div>
                            <?php } ?>
                            <?php if ($vehicle['ac']) { ?>
                                <div>
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512"><!--!Font Awesome Free 6.5.2 by @fontawesome - https://fontawesome.com License - https://fontawesome.com/license/free Copyright 2024 Fonticons, Inc.-->
                                        <path d="M224 0c13.3 0 24 10.7 24 24V70.1l23-23c9.4-9.4 24.6-9.4 33.9 0s9.4 24.6 0 33.9l-57 57v76.5l66.2-38.2 20.9-77.8c3.4-12.8 16.6-20.4 29.4-17s20.4 16.6 17 29.4L373 142.2l37.1-21.4c11.5-6.6 26.2-2.7 32.8 8.8s2.7 26.2-8.8 32.8L397 183.8l31.5 8.4c12.8 3.4 20.4 16.6 17 29.4s-16.6 20.4-29.4 17l-77.8-20.9L272 256l66.2 38.2 77.8-20.9c12.8-3.4 26 4.2 29.4 17s-4.2 26-17 29.4L397 328.2l37.1 21.4c11.5 6.6 15.4 21.3 8.8 32.8s-21.3 15.4-32.8 8.8L373 369.8l8.4 31.5c3.4 12.8-4.2 26-17 29.4s-26-4.2-29.4-17l-20.9-77.8L248 297.6v76.5l57 57c9.4 9.4 9.4 24.6 0 33.9s-24.6 9.4-33.9 0l-23-23V488c0 13.3-10.7 24-24 24s-24-10.7-24-24V441.9l-23 23c-9.4 9.4-24.6 9.4-33.9 0s-9.4-24.6 0-33.9l57-57V297.6l-66.2 38.2-20.9 77.8c-3.4 12.8-16.6 20.4-29.4 17s-20.4-16.6-17-29.4L75 369.8 37.9 391.2c-11.5 6.6-26.2 2.7-32.8-8.8s-2.7-26.2 8.8-32.8L51 328.2l-31.5-8.4c-12.8-3.4-20.4-16.6-17-29.4s16.6-20.4 29.4-17l77.8 20.9L176 256l-66.2-38.2L31.9 238.6c-12.8 3.4-26-4.2-29.4-17s4.2-26 17-29.4L51 183.8 13.9 162.4c-11.5-6.6-15.4-21.3-8.8-32.8s21.3-15.4 32.8-8.8L75 142.2l-8.4-31.5c-3.4-12.8 4.2-26 17-29.4s26 4.2 29.4 17l20.9 77.8L200 214.4V137.9L143 81c-9.4-9.4-9.4-24.6 0-33.9s24.6-9.4 33.9 0l23 23V24c0-13.3 10.7-24 24-24z" />
                                    </svg>
                                    <span>A/C</span>
                                </div>
                            <?php } ?>
                        </div>
                    </div>
                    <div class="bottom">
                        <img loading="lazy" src="/assets/images/vehicles/<?php echo $vehicle['slug']; ?>.avif" alt="<?php echo $vehicle['name']; ?> thumbnail">
                    </div>
                    <?php if ($vehicle['has_discount'] === true) { ?>
                        <div class="discount-text"><?php echo $vehicle['discount_days']; ?>+ days are discounted</div>
                    <?php } ?>
                </a>
            <?php } ?>
        </div>
        <a href="/reservation/">BOOK NOW</a>
    </div>
</section>

<?php if ($show_testimonials) { ?>
    <section id="testimonial-section">
        <div class="inner">
            <div id="testimonials">
                <div class="testimonial">
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="quote-left" class="svg-inline--fa fa-quote-left fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M464 256h-80v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8c-88.4 0-160 71.6-160 160v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48zm-288 0H96v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8C71.6 32 0 103.6 0 192v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48z" />
                    </svg>
                    <div>
                        <p>Amazing rentals this is my 3rd time renting they are always on time picking me up from the airport. Clean cars and amazing service any requests they are quick to answer!! Highly recommend!!!</p>
                        <span>Dee Smith</span>
                    </div>
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="quote-right" class="svg-inline--fa fa-quote-right fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M464 32H336c-26.5 0-48 21.5-48 48v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48zm-288 0H48C21.5 32 0 53.5 0 80v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48z" />
                    </svg>
                </div>
                <div class="testimonial">
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="quote-left" class="svg-inline--fa fa-quote-left fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M464 256h-80v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8c-88.4 0-160 71.6-160 160v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48zm-288 0H96v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8C71.6 32 0 103.6 0 192v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48z" />
                    </svg>
                    <div>
                        <p>They were very prompt and friendly. The car was exactly as advertised and no hidden costs. I would definitely use this company again.</p>
                        <span>Derek Clive Matthews</span>
                    </div>
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="quote-right" class="svg-inline--fa fa-quote-right fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M464 32H336c-26.5 0-48 21.5-48 48v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48zm-288 0H48C21.5 32 0 53.5 0 80v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48z" />
                    </svg>
                </div>
                <div class="testimonial">
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="quote-left" class="svg-inline--fa fa-quote-left fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M464 256h-80v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8c-88.4 0-160 71.6-160 160v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48zm-288 0H96v-64c0-35.3 28.7-64 64-64h8c13.3 0 24-10.7 24-24V56c0-13.3-10.7-24-24-24h-8C71.6 32 0 103.6 0 192v240c0 26.5 21.5 48 48 48h128c26.5 0 48-21.5 48-48V304c0-26.5-21.5-48-48-48z" />
                    </svg>
                    <div>
                        <p>Recently rented from them. Very friendly and accommodating. The car was nice and clean like new. I would definitely recommend and will rent again from The Key’s! They’re awesome.</p>
                        <span>Barbara Ann</span>
                    </div>
                    <svg aria-hidden="true" focusable="false" data-prefix="fas" data-icon="quote-right" class="svg-inline--fa fa-quote-right fa-w-16" role="img" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M464 32H336c-26.5 0-48 21.5-48 48v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48zm-288 0H48C21.5 32 0 53.5 0 80v128c0 26.5 21.5 48 48 48h80v64c0 35.3-28.7 64-64 64h-8c-13.3 0-24 10.7-24 24v48c0 13.3 10.7 24 24 24h8c88.4 0 160-71.6 160-160V80c0-26.5-21.5-48-48-48z" />
                    </svg>
                </div>
            </div>
        </div>
    </section>
<?php } ?>

<?php include_once 'includes/footer.php' ?>