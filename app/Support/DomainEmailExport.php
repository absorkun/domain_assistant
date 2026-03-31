<?php

namespace App\Support;

use App\Models\DomainEmail;

class DomainEmailExport
{
    public function all(int $limit = 0): array
    {
        $query = DomainEmail::query()
            ->with(['user:id,email', 'domainRecord:id,domain'])
            ->orderByDesc('sent_at')
            ->orderByDesc('id');

        if ($limit > 0) {
            $query->limit($limit);
        }

        return $query
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
}
