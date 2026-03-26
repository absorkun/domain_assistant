<?php

use App\Http\Controllers\DomainEmailExportController;
use Illuminate\Support\Facades\Route;

Route::get('/domain/email/export', DomainEmailExportController::class);
