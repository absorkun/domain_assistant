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
    Route::livewire('/helpdesk/chat', 'pages::helpdesk.chat')->name('helpdesk.chat');
    Route::livewire('/helpdesk/chat/{ticket}', 'pages::helpdesk.detail')->name('helpdesk.chat.detail');
    Route::livewire('/logs/activity', 'pages::logs.activity')->name('logs.activity');

    Route::livewire('/tools/backup', 'pages::tools.backup')
        ->middleware(['role:super_admin'])
        ->name('tools.backup');
    Route::get('/tools/backup/download', BackupDownloadController::class)
        ->middleware(['role:super_admin'])
        ->name('tools.backup.download');
        
    Route::post('/logout', LogoutController::class)->name('logout');
});
