<?php

namespace App\Http\Controllers;

use App\Models\Domain;
use App\Models\DomainEmail;
use App\Support\DomainEmailExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'domain_id' => ['required', 'integer', Rule::exists('domains', 'id')],
            'status' => ['nullable', 'string', 'max:255'],
        ]);

        $domain = Domain::query()->findOrFail($validated['domain_id']);

        $domainEmail = DomainEmail::query()->create([
            'domain_id' => $domain->id,
            'user_id' => $domain->user_id,
            'status' => $validated['status'] ?? 'sent',
            'sent_at' => now(),
        ]);

        return response()->json([
            'data' => $domainEmail,
        ], 201);
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
