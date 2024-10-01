<?php
session_start();

include 'connection.php';
include 'helpers.php';

// Get the JSON data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if JSON was properly decoded
if (json_last_error() !== JSON_ERROR_NONE) {
    echo json_encode(['error' => 'Invalid JSON input']);
    exit;
}

if (isset($data['step'])) {
    $_SESSION['reservation']['step'] = $data['step'];
    unset($data['step']);
}

if ($data['action'] === 'itinerary') {
    unset($data['action']);
    $days = getDifferenceInDays($data['pickUpDate']['date'], $data['returnDate']['date']);
    $_SESSION['reservation']['itinerary'] = $data;
    $_SESSION['reservation']['itinerary']['days'] = $days;

    if (isset($_SESSION['reservation']['vehicle'])) {
        $vehicle_discount_query = "SELECT * FROM vehicle_discounts WHERE vehicle_id = {$_SESSION['reservation']['vehicle']['id']} AND `days` <= $days ORDER BY `days` DESC LIMIT 1";
        $vehicle_discount_result = mysqli_query($con, $vehicle_discount_query);
        $discount = mysqli_fetch_assoc($vehicle_discount_result);

        $_SESSION['reservation']['discount'] = $discount;
    }

    $data = $_SESSION['reservation'];
}

if (isset($data['action']) && $data['action'] === 'vehicle') {
    unset($data['action']);

    // Use prepared statements to prevent SQL injection
    $stmt = $con->prepare("SELECT * FROM vehicles WHERE id = ?");
    $stmt->bind_param('i', $data['id']);  // Assuming the id is an integer
    $stmt->execute();
    $vehicle_result = $stmt->get_result();
    $vehicle = $vehicle_result->fetch_assoc();

    $vehicle['imgSrc'] = "/assets/images/vehicles/{$vehicle['slug']}.avif";
    $_SESSION['reservation']['vehicle'] = $vehicle;

    if (isset($_SESSION['reservation']['itinerary'])) {
        $days = $_SESSION['reservation']['itinerary']['days'];
        $stmt = $con->prepare("SELECT * FROM vehicle_discounts WHERE vehicle_id = ? AND `days` <= ? ORDER BY `days` DESC LIMIT 1");
        $stmt->bind_param('ii', $data['id'], $days);
        $stmt->execute();
        $vehicle_discount_result = $stmt->get_result();
        $discount = $vehicle_discount_result->fetch_assoc();

        $_SESSION['reservation']['discount'] = $discount;
    }

    $data = $_SESSION['reservation'];
}

if (isset($data['action']) && $data['action'] === 'add_add_on') {
    $stmt = $con->prepare("SELECT * FROM add_ons WHERE id = ?");
    $stmt->bind_param('i', $data['id']);
    $stmt->execute();
    $add_on_result = $stmt->get_result();
    $add_on = $add_on_result->fetch_assoc();

    // Merge new add-on with current add-ons in the session object and sort by id
    $_SESSION['reservation']['add_ons'][$add_on['id']] = $add_on;
    uasort($_SESSION['reservation']['add_ons'], function ($a, $b) {
        return $a['id'] - $b['id'];
    });

    $data = $_SESSION['reservation'];
}

if (isset($data['action']) && $data['action'] === 'remove_add_on') {
    $stmt = $con->prepare("SELECT * FROM add_ons WHERE id = ?");
    $stmt->bind_param('i', $data['id']);
    $stmt->execute();
    $add_on_result = $stmt->get_result();
    $add_on = $add_on_result->fetch_assoc();

    // Remove add-on from current add-ons in the session object
    unset($_SESSION['reservation']['add_ons'][$add_on['id']]);

    $data = $_SESSION['reservation'];
}

if (isset($data['action']) && $data['action'] === 'get_reservation') {
    $data = $_SESSION['reservation'];
}

if (isset($data['action']) && $data['action'] === 'reset_reservation') {
    unset($_SESSION['reservation']);
}

// Send back the data as JSON
echo json_encode($data ? $data : $_POST);
