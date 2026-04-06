<?php

namespace App\Http\Controllers\Api;

use App\Mail\DomainExpiredMail;
use App\Models\Domain;
use App\Models\DomainEmail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DomainEmailController
{
    public function get(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:0'],
        ]);

        $year = (int) ($validated['year'] ?? 0);

        $from = now()->subYears($year)->toDateString();
        $to = now()->toDateString();

        $todayExpiredDomains = Domain::query()
            ->whereDate('tgl_exp', '>=', $from)
            ->whereDate('tgl_exp', '<=', $to)
            ->orderBy('tgl_exp')
            ->get();

        if ($todayExpiredDomains->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada domain yang diproses.',
                'summary' => [
                    'total' => 0,
                ],
                'data' => [],
            ]);
        }

        $result = [];

        foreach ($todayExpiredDomains as $expDomain) {
            $domainEmail = DomainEmail::query()
                ->where('domain_id', $expDomain->id)
                ->first();

            $result[] = [
                'domain' => $expDomain->domain,
                'email' => $expDomain->user?->email,
                'status' => $domainEmail?->status,
                'keterangan' => $domainEmail?->keterangan,
                'sent_at' => $domainEmail?->sent_at,
            ];
        }

        return response()->json([
            'message' => 'Data berhasil diambil.',
            'summary' => [
                'total' => $todayExpiredDomains->count(),
            ],
            'data' => $result,
        ]);
    }
    
    public function post(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'year' => ['nullable', 'integer', 'min:0'],
            'limit' => ['nullable', 'integer', 'min:0']
        ]);

        $year = (int) ($validated['year'] ?? 0);
        $from = now()->subYears($year)->toDateString();
        $to = now()->toDateString();

        $limit = (int) ($validated['limit'] ?? 100);

        $todayExpiredDomains = Domain::query()
            ->whereDate('tgl_exp', '>=', $from)
            ->whereDate('tgl_exp', '<=', $to)
            ->orderBy('tgl_exp')
            ->limit($limit)
            ->get();

        if ($todayExpiredDomains->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada domain yang diproses.',
                'summary' => [
                    'sent' => 0,
                    'failed' => 0,
                    'total' => 0,
                ],
                'data' => [],
            ]);
        }

        $sentCount = 0;
        $failedCount = 0;
        $results = [];

        foreach ($todayExpiredDomains as $expDomain) {
            $domainEmail = DomainEmail::query()->firstOrCreate(
                [
                    'domain_id' => $expDomain->id,
                ],
                [
                    'user_id' => $expDomain->user_id,
                    'status' => null,
                    'keterangan' => null,
                    'sent_at' => null,
                ],
            );

            if ($domainEmail->status === 'sent') {
                $results[] = [
                    'domain' => $expDomain->domain,
                    'status' => 'skipped',
                    'message' => 'Sudah terkirim.',
                ];

                continue;
            }

            try {
                Mail::to($expDomain->user->email)->send(new DomainExpiredMail($expDomain));

                $domainEmail->forceFill([
                    'user_id' => $expDomain->user_id,
                    'status' => 'sent',
                    'keterangan' => 'Sukses',
                    'sent_at' => now(),
                ])->save();

                $sentCount++;
                $results[] = [
                    'domain' => $expDomain->domain,
                    'status' => 'sent',
                    'message' => 'Email berhasil dikirim.',
                ];
            } catch (\Throwable $throwable) {
                $domainEmail->forceFill([
                    'user_id' => $expDomain->user_id,
                    'status' => 'error',
                    'keterangan' => $throwable->getMessage(),
                    'sent_at' => now(),
                ])->save();

                $failedCount++;
                $results[] = [
                    'domain' => $expDomain->domain,
                    'status' => 'error',
                    'message' => $throwable->getMessage(),
                ];
            }
        }

        return response()->json([
            'message' => 'Proses selesai.',
            'summary' => [
                'sent' => $sentCount,
                'failed' => $failedCount,
                'total' => $todayExpiredDomains->count(),
            ],
            'data' => $results,
        ]);
    }
}
