<?php

declare(strict_types=1);

namespace App\Services;

use App\Support\EmailSender;
use App\Support\Settings;

final class ContactService
{
    /** @param array<string, mixed> $data */
    public function send(array $data): array
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
        $email = trim((string) ($data['email'] ?? ''));
        $message = trim((string) ($data['message'] ?? ''));

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
