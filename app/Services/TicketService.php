<?php

namespace App\Services;

use App\Enums\ContactEventType;
use App\Enums\ContactPriority;
use App\Enums\ContactSource;
use App\Enums\ContactStatus;
use App\Models\Contact;
use App\Models\Entreprise;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class TicketService
{
    /**
     * The sole application entry point for new platform tickets. No mail or auth.
     * is_active_acc means User existence at creation, NOT verified/paid/active access.
     * Explicit identity links are trusted caller context, never authorization by email.
     */
    public function create(
        string $name,
        string $email,
        string $subject,
        string $message,
        ContactSource $source = ContactSource::Contact,
        ?string $ip = null,
        ?User $user = null,
        ?Entreprise $entreprise = null,
        array $metadata = [],
        ContactPriority $priority = ContactPriority::Normal,
    ): Contact {
        $name = trim($name);
        $email = trim($email);
        $subject = trim($subject);
        Validator::make(compact('name', 'email', 'subject', 'message', 'ip', 'metadata'), [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email:rfc', 'max:255'],
            'subject' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string', 'max:50000'],
            'ip' => ['nullable', 'ip'],
            'metadata' => ['array'],
        ])->validate();
        // Reject non-JSON metadata before allocating any number or persisting a ticket.
        json_encode($metadata, JSON_THROW_ON_ERROR);

        $connection = DB::connection();
        foreach ([$user, $entreprise] as $linked) {
            if ($linked && (! $linked->exists || $linked->getConnection()->getName() !== $connection->getName())) {
                throw ValidationException::withMessages(['identity' => 'Links must be persisted on the ticket database connection.']);
            }
        }
        // A trusted web User may supply a different reply address. Identity is
        // established by the caller's authenticated context, never email matching.
        if ($user && $entreprise && (int) $user->entreprise_id !== (int) $entreprise->id) {
            throw ValidationException::withMessages(['entreprise' => 'The explicit User and Entreprise must belong together.']);
        }

        return $connection->transaction(function () use ($connection, $name, $email, $subject, $message, $source, $ip, $user, $entreprise, $metadata, $priority) {
            // Increment FIRST: the write serializes concurrent allocators, including SQLite.
            // This lock stays held until the Contact and initial event are committed.
            $sequence = $connection->table('contact_ticket_sequences')->where('id', 1);
            if ($sequence->increment('next_number') !== 1) {
                throw new RuntimeException('Contact ticket sequence is missing.');
            }
            $number = (int) $sequence->value('next_number') - 1;
            $exists = User::on($connection->getName())
                ->whereRaw('LOWER(TRIM(email)) = ?', [mb_strtolower($email)])->exists();
            $contact = Contact::on($connection->getName())->create([
                'name' => $name, 'email' => $email, 'subject' => $subject, 'message' => $message,
                'source' => $source, 'ip' => $ip, 'status' => ContactStatus::Pending,
                'priority' => $priority,
                'ticket_num' => 'DT-'.str_pad((string) $number, 6, '0', STR_PAD_LEFT),
                'is_active_acc' => $exists,
                'user_id' => $user?->id, 'entreprise_id' => $entreprise?->id,
            ]);
            $contact->events()->create(['event_type' => ContactEventType::Created, 'metadata' => $metadata ?: null]);

            return $contact;
        }, 3);
    }
}
