<?php

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

    // Get data sent via front end fetch request
    $name = $data["name"];
    $email = $data["email"];
    $message = $data["message"];

    // Email values
    $subject = "Someone Has Contacted You From $company_name Website";
    $headers  = "From: no-reply@$domain\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    $body = "Some has contacted you from the $company_name website.

Name: $name

Email: $email

Message: $message";

    // Send email to Admin
    $mail_res = mail($contact_email_string, $subject, $body, $headers);

    respond([
        "success" => true,
        "message" => "success",
        "status" => 200,
        "data" => [
            "mail" => compact("to", "subject", "body", "headers", "mail_res"),
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
