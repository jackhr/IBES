<?php

session_start();

include 'connection.php';
include 'helpers.php';

// Get the JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

try {
    if ($data['h826r2whj4fi_cjz8jxs2zuwahhhk6'] !== "") {
        respond([
            "success" => false,
            "message" => "error",
            "status" => 400,
            "data" => []
        ]);
    }

    $debugging = isset($debugging_email_string);

    if ($debugging) {
        ini_set('display_errors', 1);
        ini_set('display_startup_errors', 1);
        error_reporting(E_ALL);
    }

    $first_name_trimmed = trim($data["first-name"]);
    $last_name_trimmed = trim($data["last-name"]);
    $driver_license_trimmed = trim($data["driver-license"]);
    $country_region_trimmed = trim($data["country-region"]);
    $street_trimmed = trim($data["street"]);
    $town_city_trimmed = trim($data["town-city"]);
    $state_county_trimmed = trim($data["state-county"]);
    $phone_trimmed = trim($data["phone"]);
    $email_trimmed = trim($data["email"]);
    $hotel_trimmed = null;

    // Get data sent via front end fetch request
    $first_name = mysqli_real_escape_string($con, $first_name_trimmed);
    $last_name = mysqli_real_escape_string($con, $last_name_trimmed);
    $driver_license = mysqli_real_escape_string($con, $driver_license_trimmed);
    $country_region = mysqli_real_escape_string($con, $country_region_trimmed);
    $street = mysqli_real_escape_string($con, $street_trimmed);
    $town_city = mysqli_real_escape_string($con, $town_city_trimmed);
    $state_county = mysqli_real_escape_string($con, $state_county_trimmed);
    $phone = mysqli_real_escape_string($con, $phone_trimmed);
    $email = mysqli_real_escape_string($con, $email_trimmed);
    $hotel = "NULL";
    if (is_string($data["hotel"])) {
        if (strlen($data["hotel"]) > 0) {
            $hotel_trimmed = trim($data["hotel"]);
            $hotel_sql_escaped = mysqli_real_escape_string($con, $hotel_trimmed);
            $hotel = "'$hotel_sql_escaped'";
        }
    }

    // Get data from session
    $reservation = $_SESSION['reservation'];
    $itinerary = $reservation['itinerary'];
    $vehicle = $reservation['vehicle'];
    $add_ons = $reservation['add_ons'];
    $discount = $reservation['discount'] ? $reservation['discount'] : null;

    // Caclulate what is needed
    $days = $itinerary['days'];
    $price_day = (int)$vehicle['base_price_USD'];
    if (isset($discount)) {
        $price_day = (int)$discount['price_USD'];
    }
    $vehicle_subtotal = $price_day * $days;
    $sub_total = $vehicle_subtotal + getAddOnsSubTotal($add_ons, $days, null, $vehicle);
    $timestamp = time();
    $pick_up_ts = ((int)$itinerary['pickUpDate']['ts'] / 1000);
    $drop_off_ts = ((int)$itinerary['returnDate']['ts'] / 1000);
    $pick_up_location = $itinerary['pickUpLocation'];
    $drop_off_location = $itinerary['returnLocation'];

    // Insert contact info into database
    $contact_info_query = "INSERT INTO `contact_info` (`first_name`, `last_name`, `driver_license`, `hotel`, `country_or_region`, `street`, `town_or_city`, `state_or_county`, `phone`, `email`) VALUES ('{$first_name}', '{$last_name}', '{$driver_license}', {$hotel}, '{$country_region}', '{$street}', '{$town_city}', '{$state_county}', '{$phone}', '{$email}');";
    $contact_info_result = mysqli_query($con, $contact_info_query);
    $contact_info_id = mysqli_insert_id($con);

    // Get key that doesn't exist in the database
    $key_is_safe = false;
    while (!$key_is_safe) {
        $key = generateRandomKey();
        $key_query = "SELECT * FROM `order_requests` WHERE `key` = '{$key}'";
        $key_result = mysqli_query($con, $key_query);
        $key_is_safe = mysqli_num_rows($key_result) === 0;
    }

    // Insert order request into database
    $order_request_query = "INSERT INTO `order_requests` (`key`, `pick_up`, `drop_off`, `pick_up_location`, `drop_off_location`, `confirmed`, `contact_info_id`, `sub_total`, `car_id`, `days`) VALUES ('{$key}', FROM_UNIXTIME({$pick_up_ts}), FROM_UNIXTIME({$drop_off_ts}), '{$pick_up_location}', '{$drop_off_location}', 0, {$contact_info_id}, '{$sub_total}', {$vehicle['id']}, {$days});";
    $order_request_result = mysqli_query($con, $order_request_query);
    $order_request_id = mysqli_insert_id($con);

    // Relate relevant add ons to order request
    foreach ($add_ons as $add_on_id => $add_on) {
        $order_request_add_on_query = "INSERT INTO `order_request_add_ons` (`order_request_id`, `add_on_id`) VALUES ({$order_request_id}, {$add_on_id});";
        $order_request_add_on_result = mysqli_query($con, $order_request_add_on_query);
    }

    // Generate client email body
    $client_email_body = generateEmailBody($hotel_trimmed, $first_name_trimmed, $last_name_trimmed, $country_region_trimmed, $street_trimmed, $town_city_trimmed, $state_county_trimmed, $phone_trimmed, $email_trimmed, $order_request_id, $vehicle, $add_ons, $itinerary, $days, $sub_total, $timestamp, $key, $vehicle_subtotal);

    // Generate admin email body
    $admin_email_body = generateEmailBody($hotel_trimmed, $first_name_trimmed, $last_name_trimmed, $country_region_trimmed, $street_trimmed, $town_city_trimmed, $state_county_trimmed, $phone_trimmed, $email_trimmed, $order_request_id, $vehicle, $add_ons, $itinerary, $days, $sub_total, $timestamp, $key, $vehicle_subtotal, true);

    // Send email to client
    $mail_res_client = handleSendEmail($email, $client_email_body);

    // determine admin email string
    if ($debugging) {
        $admin_email_str = $debugging_email_string;
    } else if (isset($testing_email_string)) {
        $admin_email_str = $testing_email_string;
    } else {
        $admin_email_str = $email_string;
    }

    // Send email to admin
    $mail_res_admin = handleSendEmail($admin_email_str, $admin_email_body, $email);

    if (isset($destory_session_after_ordering) && $destory_session_after_ordering === true && $debugging) session_destroy();

    $res = [
        "success" => true,
        "message" => "success",
        "status" => 200,
        "data" => [
            "mail" => compact("mail_res_client", "mail_res_admin"),
            "key" => $key,
            "debugging" => $debugging,
        ]
    ];

    if ($debugging) {
        $res["data"]["contact_info_query"] = $contact_info_query;
        $res["data"]["contact_info_result"] = $contact_info_result;
        $res["data"]["order_request_query"] = $order_request_query;
        $res["data"]["order_request_result"] = $order_request_result;
        $res["data"]["admin_email_body"] = $admin_email_body;
        $res["data"]["client_email_body"] = $client_email_body;
    }

    respond($res);
} catch (Exception $e) {
    respond([
        "success" => false,
        "message" => $e->getMessage(),
        "status" => 500,
        "data" => [$e]
    ]);
}
