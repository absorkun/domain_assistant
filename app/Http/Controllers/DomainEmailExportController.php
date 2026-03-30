<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Support\DomainEmailExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DomainEmailExportController extends Controller
{
    public function __construct(private readonly DomainEmailExport $domainEmailExport)
    {
    }

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => $this->domainEmailExport->todaySent(),
        ]);
    }

    public function today(): JsonResponse
    {
        return response()->json([
            'data' => $this->domainEmailExport->todaySent(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain' => ['required', 'string', Rule::exists('domains', 'domain')],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $domain = Domain::query()
            ->where('domain', $validated['domain'])
            ->firstOrFail();

        $domainEmail = DomainEmail::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $domain->user_id,
            'status' => $validated['status'] ?? 'sent',
            'sent_at' => now(),
            'retry1' => false,
            'retry2' => false,
            'retry3' => false,
        ]);

        return response()->json([
            'data' => $domainEmail,
        ], 201);
    }

    public function pending(): JsonResponse
    {
        $emails = DB::transaction(function () {
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

        return response()->json([
            'data' => $emails,
        ]);
    }

    public function show(string $domain): JsonResponse
    {
        $record = Domain::query()
            ->with(['user:id,email'])
            ->where('domain', $domain)
            ->firstOrFail();

        $domainEmails = DomainEmail::query()
            ->where('domain_id', $record->id)
            ->latest('sent_at')
            ->get();

        return response()->json([
            'data' => [
                'domain' => $record,
                'emails' => $domainEmails,
            ],
        ]);
    }
}
