<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Session;
use App\Repositories\RentalRepository;
use App\Support\EmailSender;
use App\Support\Settings;
use DateTime;

final class TaxiService
{
    public function __construct(private RentalRepository $rentalRepository)
    {
    }

    /** @param array<string, mixed> $data */
    public function submit(array $data): array
    {
        if (($data['h826r2whj4fi_cjz8jxs2zuwahhhk6'] ?? '') !== '') {
            return [
                'success' => false,
                'message' => 'error',
                'status' => 400,
                'data' => [],
            ];
        }

        $name = trim((string) ($data['name'] ?? ''));
        $phone = trim((string) ($data['phone'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));
        $pickUp = trim((string) ($data['pickUp'] ?? ''));
        $dropOff = trim((string) ($data['dropOff'] ?? ''));
        $passengers = trim((string) ($data['passengers'] ?? ''));

        $pickUpDate = new DateTime(trim((string) ($data['pickUpTime'] ?? '')));
        $pickUpDateTime = $pickUpDate->format('Y-m-d H:i:s.u');
        $formattedPickUpDateTime = $pickUpDate->format('F j, Y \a\t g:i A');

        $this->rentalRepository->insertTaxiRequest(
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
