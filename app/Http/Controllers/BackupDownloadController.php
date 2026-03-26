<?php

namespace App\Http\Controllers;

use App\Support\DatabaseBackup;
use Illuminate\Http\Response;

class BackupDownloadController extends Controller
{
    public function __construct(private readonly DatabaseBackup $databaseBackup)
    {
    }

    public function __invoke(): Response
    {
        $zipPath = $this->databaseBackup->createZip();

        return response()->download($zipPath, basename($zipPath))->deleteFileAfterSend(true);
    }
}
