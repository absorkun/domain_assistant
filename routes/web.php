<?php

use App\Http\Controllers\BackupDownloadController;
use App\Http\Controllers\LogoutController;
use Illuminate\Support\Facades\Route;

Route::livewire('/', 'pages::index');

Route::livewire('/login', 'pages::auth.login')
    ->middleware('guest')
    ->name('login');

Route::middleware(['auth'])->group(function () {
    Route::livewire('/dashboard', 'pages::dashboard')->name('dashboard');
    Route::livewire('/domain', 'pages::domain.index')->name('domain.search');
    Route::livewire('/domain/add', 'pages::domain.add')->name('domain.add');
    Route::livewire('/domain/expired', 'pages::domain.expired')->name('domain.expired');
    Route::livewire('/domain/email', 'pages::domain.email')->name('domain.email');
    Route::livewire('/domain/{id}', 'pages::domain.edit')->name('domain.edit');
    Route::livewire('/tools/backup', 'pages::tools.backup')->name('tools.backup');

    Route::get('/tools/backup/download', BackupDownloadController::class)->name('tools.backup.download');
    Route::post('/logout', LogoutController::class)->name('logout');
});
