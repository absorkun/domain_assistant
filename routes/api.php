<?php

use App\Http\Controllers\DomainEmailController;
use App\Http\Controllers\DomainEmailExportController;
use Illuminate\Support\Facades\Route;

// Route::get('/domain/email/get', [DomainEmailExportController::class, 'index']);
// Route::post('/domain/email/post', [DomainEmailExportController::class, 'store']);
// Route::get('/domain/email/pending', [DomainEmailExportController::class, 'pending']);
// Route::get('/domain/email/get/{domain}', [DomainEmailExportController::class, 'show']);

Route::get('/email/get', [DomainEmailController::class, 'get']);
Route::post('/email/post', [DomainEmailController::class, 'post']);
