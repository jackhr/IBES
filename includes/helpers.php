<?php

function makeAddOnDescriptionStr($add_on, $vehicles)
{
    $add_on_str = $add_on['description'];
    if ($add_on['name'] !== "Collision Insurance") return $add_on_str;

    $insurance_strings_arr = [];
    foreach ($vehicles as $vehicle) {
        $key = "USD\${$vehicle['insurance']}/day";
        if (isset($insurance_strings_arr[$key])) {
            $insurance_strings_arr[$key] = $insurance_strings_arr[$key] . " / " . $vehicle['name'];
        } else {
            $insurance_strings_arr[$key] = $vehicle['name'];
        }
    }

    foreach ($insurance_strings_arr as $key => $insurance_str) {
        $add_on_str .= "<br><br><b>{$key}</b>: <i>{$insurance_str}</i>";
    }

    return $add_on_str;
}

function getDifferenceInDays($pickUpDate, $returnDate)
{
    $start = new DateTime($pickUpDate);
    $end = new DateTime($returnDate);

    // Set the time to midnight for both dates to count full days
    $start->setTime(0, 0);
    $end->setTime(0, 0);

    // Calculate the difference in days
    $diff = $start->diff($end);

    // Return the number of days plus one to include the pickup day
    return $diff->days;
}

function makePriceString($rate, $days = 1, $currency = "USD")
{
    if (!isset($rate)) $rate = 0;
    // Currency can only be USD or EC
    if ($currency !== "USD" && $currency !== "EC") $currency = "USD";
    return '$' . $currency . ((int)$rate * $days);
}

function generateRandomKey($length = 24)
{
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[rand(0, strlen($characters) - 1)];
    }
    return $key;
}

function respond($res)
{
    echo json_encode($res);
    die();
}

function getNameTdStr($add_on, $days)
{
    $name_td_str = "1 x {$add_on['name']}";
    if ($add_on['fixed_price'] !== "1") $name_td_str .= " for $days day(s)";
    return $name_td_str;
}

function getAddOnCostForTotalDays($add_on, $days = 1, $vehicle = null)
{
    $new_cost = (int)$add_on['cost'];
    if ($add_on['name'] === "Collision Insurance") {
        if (isset($vehicle) && isset($vehicle['insurance'])) {
            $new_cost = (int)$vehicle['insurance'] * $days;
        } else {
            $new_cost = 0;
        }
    }
    return $new_cost;
}

function getAddOnsSubTotal($add_ons = null, $days = null, $itinerary = null, $vehicle = null)
{
    $sub_total = 0;
    if (isset($add_ons)) {
        if (!isset($days)) {
            $days = 1;
            if (isset($itinerary)) {
                $days = getDifferenceInDays($itinerary['pickUpDate']['date'], $itinerary['returnDate']['date']);
            }
        }

        foreach ($add_ons as $add_on) {
            $sub_total += getAddOnCostForTotalDays($add_on, $days, $vehicle);
        }
    }

    return $sub_total;
}

function handleSendEmail($email_str = "", $body = "", $reply_to = "")
{
    $headers  = "From: bookings@ibescarrental.com\r\n";
    if (strlen($reply_to) > 0) $headers .= "Reply-To: $reply_to\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
    $subject = "Car Rental Request at Ibes Car Rental";

    return mail($email_str, $subject, $body, $headers);
}

function generateAddress($first_name, $last_name, $country_region, $street, $town_city, $state_county, $phone, $email)
{
    return "{$first_name} {$last_name}<br>{$street}<br>{$town_city}, {$state_county}<br>{$country_region}<br><a href=\"tel:{$phone}\" style=\"color:#586771;font-weight:normal;text-decoration:underline\" target=\"_blank\">{$phone}</a><br><a href=\"mailto:$email\" target=\"_blank\">{$email}</a>";
}

function generateEmailBody($hotel, $first_name, $last_name, $country_region, $street, $town_city, $state_county, $phone, $email, $order_request_id, $vehicle, $add_ons, $itinerary, $days, $sub_total, $timestamp, $key, $vehicle_subtotal, $is_admin_email = false)
{
    $fontFamily = 'font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;';

    $add_ons_rows = "";

    foreach ($add_ons as $add_on) {
        $add_on_cost = getAddOnCostForTotalDays($add_on, $days, $vehicle);
        $quantity = $add_on['fixed_price'] !== "1" ? $days : 1;
        $add_ons_rows .= '<tr>
            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;word-wrap:break-word">' . $add_on['name'] . '</td>
            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">' . $quantity . '</td>
            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">
                <span><u></u>USD<span>$</span>' . $add_on_cost . '<u></u></span>
            </td>
        </tr>';
    }

    if (is_null($hotel)) $hotel = "<i>Not provided</i>";

    $intro = '<p style="margin:0 0 16px">Hi ' . $first_name . ' ' . $last_name . ',</p>
    <p style="margin:0 0 16px">Just to let you know - we\'ve received your order #' . $order_request_id . ', and it is now being processed.</p>
    <p style="margin:0 0 16px">Pay with cash or card when you pick-up your vehicle.</p>';

    if ($is_admin_email) {
        $intro = '<p style="margin:0 0 16px">Hi Irwin,</p>
        <p style="margin:0 0 16px">Just to let you know, ' . $first_name . ' ' . $last_name . ' has just put in an order request.</p>
        <p style="margin:0 0 16px">The client\'s email address is ' . $email . '</p>
        <p style="margin:0 0 16px">Below are the details of the order:</p>';
    }

    $body = '
        <div style="background-color:#f7f7f7;margin:0;padding:70px 0;width:100%">
            <table border="0" cellpadding="0" cellspacing="0" width="600" style="background-color:#ffffff;border:1px solid #dedede;border-radius:3px;margin: auto;">
                <tbody>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="100%" style="background-color:#586771;color:#ffffff;border-bottom:0;font-weight:bold;line-height:100%;vertical-align:middle;' . $fontFamily . 'border-radius:3px 3px 0 0">
                                <tbody>
                                    <tr>
                                        <td style="padding:36px 48px;display:block">
                                            <h1 style="' . $fontFamily . 'font-size:30px;font-weight:300;line-height:150%;margin:0;text-align:center;color:#ffffff;background-color:inherit">Thank you for your rental request</h1>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="0" cellspacing="0" width="600">
                                <tbody>
                                    <tr>
                                        <td valign="top" style="background-color:#ffffff">
                                            <table border="0" cellpadding="20" cellspacing="0" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td valign="top" style="padding:48px 48px 32px">
                                                            <div style="color:#636363;' . $fontFamily . 'font-size:14px;line-height:150%;text-align:left">
                                                                ' . $intro . '
                                                                <h2 style="color:#586771;display:block;' . $fontFamily . 'font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">Order #' . $order_request_id . ' (<time datetime="' . gmdate("Y-m-d\TH:i:s\+00:00", $timestamp) . '">' . (date("F d, Y", $timestamp)) . '</time>)</h2>
                                                                <table cellspacing="0" cellpadding="6" border="1" style="color:#636363;border:1px solid #e5e5e5;vertical-align:middle;width:100%;font-family:\"Helvetica Neue\"Helvetica,Roboto,Arial,sans-serif">
                                                                    <thead>
                                                                        <tr>
                                                                            <th scope="col" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Product</th>
                                                                            <th scope="col" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Quantity</th>
                                                                            <th scope="col" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Price</th>
                                                                        </tr>
                                                                    </thead>
                                                                    <tbody>
                                                                        <tr>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;word-wrap:break-word">' . $vehicle['name'] . '</td>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">' . $days . ' days</td>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">
                                                                                <span><u></u>USD<span>$</span>' . $vehicle_subtotal . '<u></u></span>
                                                                            </td>
                                                                        </tr>
                                                                        ' . $add_ons_rows . '
                                                                    </tbody>
                                                                    <tfoot>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Hotel</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $hotel . '</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Pickup date</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $itinerary['pickUpDate']['altValue'] . '</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Pickup location</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $itinerary['pickUpLocation'] . '</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Return date</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $itinerary['returnDate']['altValue'] . '</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Return location</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $itinerary['returnLocation'] . '</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">Subtotal</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px"><span><u></u>USD<span>$</span>' . $sub_total . '<u></u></span></td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Payment method</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Pay at Pickup</td>
                                                                        </tr>
                                                                        <tr>
                                                                            <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Total</th>
                                                                            <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><span><u></u>USD<span>$</span>' . $sub_total . '<u></u></span></td>
                                                                        </tr>
                                                                    </tfoot>
                                                                </table>
                                                                <table cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;padding:0">
                                                                    <tbody>
                                                                        <tr>
                                                                            <td valign="top" width="50%" style="text-align:left;' . $fontFamily . 'border:0;padding:0">
                                                                                <h2 style="color:#586771;display:block;' . $fontFamily . 'font-size:18px;font-weight:bold;line-height:130%;margin:18px 0;text-align:left">Billing address</h2>

                                                                                <address style="' . $fontFamily . 'padding:12px;color:#636363;border:1px solid #e5e5e5">' . generateAddress($first_name, $last_name, $country_region, $street, $town_city, $state_county, $phone, $email) . ' </address>
                                                                            </td>
                                                                        </tr>
                                                                    </tbody>
                                                                </table>
                                                                <h2 style="text-align: center;margin: 40px auto 0;">Click below to view your full reservation!</h2>
                                                                <a href="https://www.ibescarrental.com/confirmation/?key=' . $key . '" style="width: max-content;padding: 12px;margin: 40px auto;font-weight: 800;font-size: 18px;letter-spacing: 0.5px;border: solid 1px #586771;border-radius: 6px;display: block;color: black !important;text-decoration: none !important;box-shadow: 0 0 10px 1px #58677150;">' . $key . '</a>
                                                                <p style="margin:20px 0;text-align:center;line-height: 1.5;font-size: 14px;">Link not working? Copy and paste the following url into your browser to view your full rental summary on our website Can\'t wait to see you on the roads!</p>
                                                                <p style="text-align:center;margin-bottom: 40px;"><strong>https://www.ibescarrental.com/confirmation/?key=' . $key . '</strong></p>
                                                                <p style="margin:0 0 16px;text-align: center;">Thanks for using <a href="https://www.ibescarrental.com/" target="_blank">www.ibescarrental.com</a>!</p>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
            <table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%">
                <tbody>
                    <tr>
                        <td align="center" valign="top">
                            <table border="0" cellpadding="10" cellspacing="0" width="600">
                                <tbody>
                                    <tr>
                                        <td valign="top" style="padding:0;border-radius:6px">
                                            <table border="0" cellpadding="10" cellspacing="0" width="100%">
                                                <tbody>
                                                    <tr>
                                                        <td colspan="2" valign="middle" style="border-radius:6px;border:0;color:#8a8a8a;' . $fontFamily . 'font-size:12px;line-height:150%;text-align:center;padding:24px 0">
                                                            <p style="margin:0 0 16px">Ibes Car Rental</p>
                                                        </td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    ';

    return $body;
}
