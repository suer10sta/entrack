<?php

namespace App\Mail;

use App\Models\Entreprise;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Message;
use RuntimeException;

/** Send company mail from the platform's verified address, with replies to the company. */
class CompanySender
{
    public static function from(?Entreprise $company): Address
    {
        $address = trim((string) config('mail.from.address'));
        if (! filter_var($address, FILTER_VALIDATE_EMAIL)) {
            throw new RuntimeException('Configure a verified DalyTrac sender in MAIL_FROM_ADDRESS before sending company email.');
        }

        return new Address($address, $company?->name ?: (config('mail.from.name') ?: 'DalyTrac'));
    }

    /** @return array<Address> */
    public static function replyTo(?Entreprise $company): array
    {
        $address = trim((string) $company?->email);

        return filter_var($address, FILTER_VALIDATE_EMAIL)
            ? [new Address($address, $company?->name ?: '')]
            : [];
    }

    public static function apply(Message|Mailable $message, ?Entreprise $company): void
    {
        $sender = self::from($company);
        $message->from($sender->address, $sender->name);
        foreach (self::replyTo($company) as $replyTo) {
            $message->replyTo($replyTo->address, $replyTo->name);
        }
    }
}
