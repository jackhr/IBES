<?php

include 'connection.php';
include 'helpers.php';

session_start();

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

    // Get data sent via front end fetch request
    $name = trim($data["name"]);
    $phone = trim($data["phone"]);
    $email = trim($data["email"]);
    $message = trim($data["message"]);
    $pickUp = trim($data['pickUp']);
    $dropOff = trim($data['dropOff']);
    $passengers = trim($data['passengers']);
    $date = new DateTime(trim($data['pickUpTime']));
    $pickUpDateTime = $date->format('Y-m-d H:i:s.u');
    $formattedPickUpDateTime = $date->format('F j, Y \a\t g:i A');

    $name = mysqli_real_escape_string($con, $name);
    $phone = mysqli_real_escape_string($con, $phone);
    $email = mysqli_real_escape_string($con, $email);
    $message = mysqli_real_escape_string($con, $message);
    $pickUp = mysqli_real_escape_string($con, $pickUp);
    $dropOff = mysqli_real_escape_string($con, $dropOff);
    $passengers = mysqli_real_escape_string($con, $passengers);
    $pickUpTime = mysqli_real_escape_string($con, $pickUpDateTime);

    $taxi_request_query = "INSERT INTO `taxi_requests` (`customer_name`, `customer_phone`, `pickup_location`, `dropoff_location`, `pickup_time`, `number_of_passengers`, `special_requirements`, `created_at`) VALUES ('$name', '$phone', '$pickUp', '$dropOff', '$pickUpTime', '$passengers', '$message', CURRENT_TIMESTAMP);";
    $taxi_request_result = mysqli_query($con, $taxi_request_query);
    $taxi_request_id = mysqli_insert_id($con);


    // Email values
    $subject = "$company_name Website Taxi Reservation";
    $headers  = "From: no-reply@$domain\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $body = "Some has requested a taxi from $company_name website.

Name: $name

Email: $email

Phone: $phone

Pick Up Location: $pickUp

Drop Off Location: $dropOff

Number of Passengers: $passengers

Time of Pick Up: $formattedPickUpDateTime

Special Requirements: $message";

    // Send email to Admin
    $mail_res = mail($contact_email_string, $subject, $body, $headers);

    unset($_SESSION['reservation']);

    respond([
        "success" => true,
        "message" => "success",
        "status" => 200,
        "data" => [
            "mail" => compact("contact_email_string", "subject", "body", "headers", "mail_res"),
            "data" => $data,
        ]
    ]);
} catch (Exception $e) {
    respond([
        "success" => false,
        "message" => $e->getMessage(),
        "status" => 500,
        "data" => [$e]
    ]);
}
