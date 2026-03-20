<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\TaxiRequestRepository;
use App\Support\EmailSender;
use App\Support\Settings;
use App\Support\Validator;

final class TaxiService
{
    public function __construct(private TaxiRequestRepository $taxiRequestRepository)
    {
    }

    /** @param array<string, mixed> $data */
    public function submit(array $data): array
    {
        $name = Validator::requiredString($data, ['name'], 'Name', 2, 120);
        $phone = Validator::requiredPhone($data, ['phone'], 'Phone');
        $email = Validator::requiredEmail($data, ['email'], 'Email');
        $message = Validator::optionalString($data, ['message'], 'Special requirements', 1500);
        $pickUp = Validator::requiredString($data, ['pickUp'], 'Pick up location', 2, 200);
        $dropOff = Validator::requiredString($data, ['dropOff'], 'Drop off location', 2, 200);
        $passengers = Validator::requiredInt($data, ['passengers'], 'Passengers', 1, 30);

        $pickUpDate = Validator::requiredDateTime($data, ['pickUpTime'], 'Pick up time');
        $pickUpDateTime = $pickUpDate->format('Y-m-d H:i:s');
        $formattedPickUpDateTime = $pickUpDate->format('F j, Y \a\t g:i A');

        $this->taxiRequestRepository->insertTaxiRequest(
            $name,
            $phone,
            $pickUp,
            $dropOff,
            $pickUpDateTime,
            $passengers,
            $message
        );

        $companyName = Settings::companyName();
        $domain = Settings::domain();
        $to = Settings::contactEmailString();

        $subject = "$companyName Website Taxi Reservation";
        $body = "Some has requested a taxi from $companyName website.\n\nName: $name\n\nEmail: $email\n\nPhone: $phone\n\nPick Up Location: $pickUp\n\nDrop Off Location: $dropOff\n\nNumber of Passengers: $passengers\n\nTime of Pick Up: $formattedPickUpDateTime\n\nSpecial Requirements: $message";

        $mailResult = EmailSender::sendPlainText($to, $subject, $body, "no-reply@$domain", $email);

        Session::clearReservation();

        return [
            'success' => true,
            'message' => 'success',
            'status' => 200,
            'data' => [
                'mail' => [
                    'contact_email_string' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'mail_res' => $mailResult,
                ],
                'data' => $data,
            ],
        ];
    }
}
