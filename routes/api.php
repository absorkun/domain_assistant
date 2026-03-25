<?php

use App\Models\DomainEmail;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Route;

Route::get('/domain/email/export', function () {
    return response()->json([
        'data' => DomainEmail::query()
            ->with(['user:id,email', 'domainRecord:id,domain'])
            ->whereDate('sent_at', Carbon::today())
            ->where('status', 'sent')
            ->orderByDesc('sent_at')
            ->limit(10)
            ->get()
            ->map(function ($email) {
                return [
                    'domain' => data_get($email, 'domainRecord.domain'),
                    'email' => data_get($email, 'user.email'),
                    'status' => $email->status,
                    'sent_at' => $email->sent_at?->toDateTimeString(),
                ];
            }),
    ]);
});
