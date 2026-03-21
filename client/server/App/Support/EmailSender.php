<?php

declare(strict_types=1);

namespace App\Support;

final class EmailSender
{
    public static function sendPlainText(string $to, string $subject, string $body, string $from, string $replyTo = ''): bool
    {
        $headers = "From: $from\r\n";

        if ($replyTo !== '') {
            $headers .= "Reply-To: $replyTo\r\n";
        }

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

        return mail($to, $subject, $body, $headers);
    }

    public static function sendHtml(string $to, string $subject, string $body, string $from, string $replyTo = ''): bool
    {
        $headers = "From: $from\r\n";

        if ($replyTo !== '') {
            $headers .= "Reply-To: $replyTo\r\n";
        }

        $headers .= "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";

        return mail($to, $subject, $body, $headers);
    }
}
