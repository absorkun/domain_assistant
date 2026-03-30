<?php

use App\Http\Controllers\DomainEmailExportController;
use Illuminate\Support\Facades\Route;

Route::get('/domain/email/get', [DomainEmailExportController::class, 'index']);
Route::post('/domain/email/post', [DomainEmailExportController::class, 'store']);
Route::get('/domain/email/get/{domain}', [DomainEmailExportController::class, 'show']);
