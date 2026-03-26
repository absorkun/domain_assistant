<?php

namespace App\Http\Controllers;

use App\Support\DomainEmailExport;
use Illuminate\Http\JsonResponse;

class DomainEmailExportController extends Controller
{
    public function __construct(private readonly DomainEmailExport $domainEmailExport)
    {
    }

    public function __invoke(): JsonResponse
    {
        return response()->json([
            'data' => $this->domainEmailExport->todaySent(),
        ]);
    }
}
