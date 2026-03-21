<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\EmailSender;
use App\Support\Settings;
use App\Support\Validator;

final class ContactService
{
    /** @param array<string, mixed> $data */
    public function send(array $data): array
    {
        $name = Validator::requiredString($data, ['name'], 'Name', 2, 120);
        $email = Validator::requiredEmail($data, ['email'], 'Email');
        $message = Validator::requiredString($data, ['message'], 'Message', 10, 4000);

        $companyName = Settings::companyName();
        $domain = Settings::domain();
        $to = Settings::contactEmailString();

        $subject = "Someone Has Contacted You From $companyName Website";
        $body = "Some has contacted you from the $companyName website.\n\nName: $name\n\nEmail: $email\n\nMessage: $message";

        $mailResult = EmailSender::sendPlainText($to, $subject, $body, "no-reply@$domain", $email);

        return [
            'success' => true,
            'message' => 'success',
            'status' => 200,
            'data' => [
                'mail' => [
                    'to' => $to,
                    'subject' => $subject,
                    'body' => $body,
                    'mail_res' => $mailResult,
                ],
                'data' => $data,
            ],
        ];
    }
}
