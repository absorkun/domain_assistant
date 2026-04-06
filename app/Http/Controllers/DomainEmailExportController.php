<?php

namespace App\Http\Controllers;

use App\Mail\DomainExpiredMail;
use App\Models\Domain;
use App\Models\DomainEmail;
use App\Support\DomainEmailExport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class DomainEmailExportController extends Controller
{
    public function __construct(private readonly DomainEmailExport $domainEmailExport)
    {
    }

    public function index(Request $request): JsonResponse
    {
        $limit = max(0, (int) $request->input('limit', 0));

        return response()->json([
            'data' => $this->domainEmailExport->all($limit),
        ]);
    }

    public function post(Request $request)
    {
        $todayExpiredDomains = Domain::query()
            ->whereDate('tgl_exp', '=', now()->subYear())
            ->get();
        
        foreach ($todayExpiredDomains as $expDomain) {
            $domainEmail = DomainEmail::query()
                ->where('domain_id', $expDomain->id)
                ->firstOrFail();
            
            if ($domainEmail->status == 'sent') return;

            Mail::to($domainEmail->user->email)->send(new DomainExpiredMail($domainEmail->expDomain));
        }
    }

    public function store(Request $request): JsonResponse
    {
        $result = $this->domainEmailExport->store($request);

        return response()->json([
            'data' => $result['domainEmail'],
            'message' => $result['domainEmail']->status === 'sent'
                ? 'Domain email berhasil dikirim.'
                : 'Domain email gagal dikirim.',
            'error' => $result['errorMessage'],
        ], 201);
    }

    public function show(string $domain): JsonResponse
    {
        return response()->json([
            'data' => $this->domainEmailExport->show($domain),
        ]);
    }

    public function pending(): JsonResponse
    {
        $emails = $this->domainEmailExport->pending();

        return response()->json([
            'data' => $emails,
            'message' => 'Domain email pending/error berhasil diambil.',
        ]);
    }
}
