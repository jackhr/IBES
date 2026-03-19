<?php

declare(strict_types=1);

namespace App\Support;

final class ReservationEmailBuilder
{
    /**
     * @param array<string, mixed> $vehicle
     * @param array<int|string, array<string, mixed>>|null $addOns
     * @param array<string, mixed> $itinerary
     */
    public static function build(
        ?string $hotel,
        string $firstName,
        string $lastName,
        string $countryRegion,
        string $street,
        string $townCity,
        string $stateCounty,
        string $phone,
        string $email,
        int $orderRequestId,
        array $vehicle,
        ?array $addOns,
        array $itinerary,
        int $days,
        int $subTotal,
        int $timestamp,
        string $key,
        int $vehicleSubtotal,
        bool $isAdminEmail = false
    ): string {
        $fontFamily = 'font-family:"Helvetica Neue",Helvetica,Roboto,Arial,sans-serif;';

        $addOnRows = '';

        if (is_array($addOns)) {
            foreach ($addOns as $addOn) {
                if (!is_array($addOn)) {
                    continue;
                }

                $addOnCost = ReservationMath::getAddOnCostForTotalDays($addOn, $days, $vehicle);
                $quantity = ($addOn['fixed_price'] ?? '0') !== '1' ? $days : 1;

                $addOnRows .= '<tr>
                    <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;word-wrap:break-word">' . ($addOn['name'] ?? '') . '</td>
                    <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">' . $quantity . '</td>
                    <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">
                        <span><u></u>USD<span>$</span>' . $addOnCost . '<u></u></span>
                    </td>
                </tr>';
            }
        }

        if ($hotel === null || $hotel === '') {
            $hotel = '<i>Not provided</i>';
        }

        $intro = '<p style="margin:0 0 16px">Hi ' . $firstName . ' ' . $lastName . ',</p>
        <p style="margin:0 0 16px">Just to let you know - we\'ve received your order #' . $orderRequestId . ', and it is now being processed.</p>
        <p style="margin:0 0 16px">Pay with cash or card when you pick-up your vehicle.</p>';

        if ($isAdminEmail) {
            $intro = '<p style="margin:0 0 16px">Hi Irwin,</p>
            <p style="margin:0 0 16px">Just to let you know, ' . $firstName . ' ' . $lastName . ' has just put in an order request.</p>
            <p style="margin:0 0 16px">The client\'s email address is ' . $email . '</p>
            <p style="margin:0 0 16px">Below are the details of the order:</p>';
        }

        return '
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
                                                                    <h2 style="color:#586771;display:block;' . $fontFamily . 'font-size:18px;font-weight:bold;line-height:130%;margin:0 0 18px;text-align:left">Order #' . $orderRequestId . ' (<time datetime="' . gmdate('Y-m-d\TH:i:s\+00:00', $timestamp) . '">' . date('F d, Y', $timestamp) . '</time>)</h2>
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
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;word-wrap:break-word">' . ($vehicle['name'] ?? '') . '</td>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">' . $days . ' days</td>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;padding:12px;text-align:left;vertical-align:middle;font-family:Helvetica,Roboto,Arial,sans-serif">
                                                                                    <span><u></u>USD<span>$</span>' . $vehicleSubtotal . '<u></u></span>
                                                                                </td>
                                                                            </tr>
                                                                            ' . $addOnRows . '
                                                                        </tbody>
                                                                        <tfoot>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Hotel</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . $hotel . '</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Pickup date</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . (($itinerary['pickUpDate']['altValue'] ?? '')) . '</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Pickup location</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . (($itinerary['pickUpLocation'] ?? '')) . '</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Return date</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . (($itinerary['returnDate']['altValue'] ?? '')) . '</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Return location</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">' . (($itinerary['returnLocation'] ?? '')) . '</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px">Subtotal</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left;border-top-width:4px"><span><u></u>USD<span>$</span>' . $subTotal . '<u></u></span></td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Payment method</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Pay at Pickup</td>
                                                                            </tr>
                                                                            <tr>
                                                                                <th scope="row" colspan="2" style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left">Total</th>
                                                                                <td style="' . $fontFamily . 'color:#636363;border:1px solid #e5e5e5;vertical-align:middle;padding:12px;text-align:left"><span><u></u>USD<span>$</span>' . $subTotal . '<u></u></span></td>
                                                                            </tr>
                                                                        </tfoot>
                                                                    </table>
                                                                    <table cellspacing="0" cellpadding="0" border="0" style="width:100%;vertical-align:top;padding:0">
                                                                        <tbody>
                                                                            <tr>
                                                                                <td valign="top" width="50%" style="text-align:left;' . $fontFamily . 'border:0;padding:0">
                                                                                    <h2 style="color:#586771;display:block;' . $fontFamily . 'font-size:18px;font-weight:bold;line-height:130%;margin:18px 0;text-align:left">Billing address</h2>
                                                                                    <address style="' . $fontFamily . 'padding:12px;color:#636363;border:1px solid #e5e5e5">' . self::buildAddress($firstName, $lastName, $countryRegion, $street, $townCity, $stateCounty, $phone, $email) . '</address>
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
    }

    private static function buildAddress(
        string $firstName,
        string $lastName,
        string $countryRegion,
        string $street,
        string $townCity,
        string $stateCounty,
        string $phone,
        string $email
    ): string {
        return "$firstName $lastName<br>$street<br>$townCity, $stateCounty<br>$countryRegion<br><a href=\"tel:$phone\" style=\"color:#586771;font-weight:normal;text-decoration:underline\" target=\"_blank\">$phone</a><br><a href=\"mailto:$email\" target=\"_blank\">$email</a>";
    }
}
