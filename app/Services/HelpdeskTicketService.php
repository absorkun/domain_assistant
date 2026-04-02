<?php

namespace App\Services;

use App\Enums\HelpdeskStatus;
use App\Models\HelpdeskTicket;
use Illuminate\Support\Collection;

class HelpdeskTicketService
{
    public function listForUser(int $userId, ?string $day = null): Collection
    {
        $query = HelpdeskTicket::query()
            ->where('user_id', $userId)
            ->latest('last_message_at')
            ->latest('id');

        if ($day) {
            $query->whereDate('created_at', $day);
        }

        return $query->get();
    }

    public function createTicket(array $data, int $userId, string $receivedByName): HelpdeskTicket
    {
        return HelpdeskTicket::query()->create([
            'user_id' => $userId,
            'domain' => $data['domain'] ?: null,
            'reporter_name' => $data['reporterName'],
            'reporter_email' => $data['reporterEmail'],
            'reporter_phone' => $data['reporterPhone'] ?: null,
            'received_by_user_id' => $userId,
            'received_by_name' => $receivedByName,
            'report_body' => $data['reportBody'],
            'subject' => $data['subject'],
            'status' => $data['status'],
            'last_message_at' => now(),
        ]);
    }

    public function updateStatus(HelpdeskTicket $ticket, string $status): HelpdeskTicket
    {
        $validated = Validator::make([
            'status' => $status,
        ], [
            'status' => ['required', Rule::enum(HelpdeskStatus::class)],
        ])->validate();

        $ticket->update([
            'status' => $validated['status'],
        ]);

        return $ticket;
    }
}
