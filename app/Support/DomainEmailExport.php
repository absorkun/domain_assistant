<?php

namespace App\Support;

use App\Models\DomainEmail;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;

class DomainEmailExport
{
    public function todaySent(): array
    {
        return DomainEmail::query()
            ->with(['user:id,email', 'domainRecord:id,domain'])
            ->whereDate('sent_at', Carbon::today())
            ->where('status', 'sent')
            ->orderByDesc('sent_at')
            ->limit(10)
            ->get()
            ->map(function ($email): array {
                return [
                    'domain' => data_get($email, 'domainRecord.domain'),
                    'email' => data_get($email, 'user.email'),
                    'status' => $email->status,
                    'sent_at' => $email->sent_at?->toDateTimeString(),
                ];
            })
            ->all();
    }

    public function all(): Collection
    {
        return DomainEmail::all();
    }
}
