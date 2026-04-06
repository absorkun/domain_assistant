<?php

namespace App\Support;

use App\Mail\DomainExpiredMail;
use App\Models\Domain;
use App\Models\DomainEmail;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

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

    public function store(Request $request): array
    {
        if ($request->boolean('batch')) {
            return $this->storeBatch($request);
        }

        $validated = $request->validate([
            'domain' => ['required', 'string', 'exists:domains,domain'],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $domain = Domain::query()
            ->where('domain', $validated['domain'])
            ->firstOrFail();

        if (! $this->canSendToday($domain)) {
            throw ValidationException::withMessages([
                'domain' => 'Domain ini sudah mencapai batas retry hari ini. Coba lagi besok.',
            ]);
        }

        if ($this->hasSentEmail($domain)) {
            throw ValidationException::withMessages([
                'domain' => 'Domain ini sudah pernah dikirim. Tidak bisa dikirim lagi.',
            ]);
        }

        return $this->sendDomainEmail($domain);
    }

    public function show(string $domain): array
    {
        $record = Domain::query()
            ->with(['user:id,email'])
            ->where('domain', $domain)
            ->firstOrFail();

        $domainEmails = DomainEmail::query()
            ->where('domain_id', $record->id)
            ->latest('sent_at')
            ->get();

        return [
            'domain' => $record,
            'emails' => $domainEmails,
        ];
    }

    public function pending(): Collection
    {
        return DB::transaction(function (): Collection {
            $emails = DomainEmail::query()
                ->where(function ($query): void {
                    $query->whereNull('status')
                        ->orWhere('status', 'error');
                })
                ->where(function ($query): void {
                    $query->where('retry1', false)
                        ->orWhere('retry2', false)
                        ->orWhere('retry3', false);
                })
                ->orderBy('sent_at')
                ->lockForUpdate()
                ->get();

            foreach ($emails as $email) {
                if ($email->status !== 'error') {
                    continue;
                }

                if (! $email->retry1) {
                    $email->forceFill(['retry1' => true])->save();

                    continue;
                }

                if (! $email->retry2) {
                    $email->forceFill(['retry2' => true])->save();

                    continue;
                }

                if (! $email->retry3) {
                    $email->forceFill(['retry3' => true])->save();
                }
            }

            return $emails->load(['domainRecord:id,domain', 'user:id,email']);
        });
    }

    /**
     * @return array{domainEmail: DomainEmail, errorMessage: ?string}
     */
    public function sendDomainEmail(Domain $domain): array
    {
        $status = 'sent';
        $retry1 = false;
        $retry2 = false;
        $retry3 = false;
        $errorMessage = null;

        try {
            Mail::to($domain->user->email)->send(new DomainExpiredMail($domain));
        } catch (\Throwable $throwable) {
            report($throwable);
            $status = 'error';
            $errorMessage = $throwable->getMessage();

            $failedAttempts = DomainEmail::query()
                ->where('domain_id', $domain->id)
                ->where('status', 'error')
                ->count();

            if ($failedAttempts === 0) {
                $retry1 = true;
            } elseif ($failedAttempts === 1) {
                $retry2 = true;
            } else {
                $retry3 = true;
            }
        }

        $domainEmail = DomainEmail::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $domain->user_id,
            'status' => $status,
            'keterangan' => $errorMessage ?? 'Sukses',
            'sent_at' => now(),
            'retry1' => $retry1,
            'retry2' => $retry2,
            'retry3' => $retry3,
        ]);

        return [
            'domainEmail' => $domainEmail,
            'errorMessage' => $errorMessage,
        ];
    }

    public function canSendToday(Domain $domain): bool
    {
        $today = Carbon::today();

        $latestRetry = DomainEmail::query()
            ->where('domain_id', $domain->id)
            ->whereDate('sent_at', $today)
            ->where('retry3', true)
            ->latest('sent_at')
            ->first();

        return $latestRetry === null;
    }

    public function hasSentEmail(Domain $domain): bool
    {
        return DomainEmail::query()
            ->where('domain_id', $domain->id)
            ->where('status', 'sent')
            ->exists();
    }

    /**
     * @return array{data: array<int, DomainEmail>, meta: array{limit: int, count: int, source: string}, message: string}
     */
    private function storeBatch(Request $request): array
    {
        $limit = max(1, min(100, (int) $request->input('limit', 100)));

        $domains = Domain::query()
            ->with(['emails:id,domain_id,status,sent_at,created_at'])
            ->whereNotNull('tgl_exp')
            ->orderBy('tgl_exp')
            ->limit($limit)
            ->get();

        $created = [];

        foreach ($domains as $domain) {
            $latestEmail = $domain->emails->sortByDesc(
                fn ($email): int => $email->sent_at?->getTimestamp()
                    ?? $email->created_at?->getTimestamp()
                    ?? 0
            )->first();

            if ($latestEmail && $latestEmail->status !== null && $latestEmail->status !== 'error') {
                continue;
            }

            if (! $this->canSendToday($domain)) {
                continue;
            }

            if ($this->hasSentEmail($domain)) {
                continue;
            }

            $result = $this->sendDomainEmail($domain);

            $created[] = $result['domainEmail'];
        }

        return [
            'data' => $created,
            'meta' => [
                'limit' => $limit,
                'count' => count($created),
                'source' => 'expired_domain_batch',
            ],
            'message' => 'Batch domain email berhasil dibuat.',
        ];
    }
}
