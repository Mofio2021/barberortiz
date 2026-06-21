<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Models\Service;

Route::get('/', function () {
    $services = Service::active()->orderBy('name')->get();
    return view('landing', compact('services'));
});

Route::get('/login', fn() => redirect()->route('filament.admin.auth.login'))->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/ticket/venta/{sale}',         [TicketController::class, 'venta'])->name('ticket.venta');
    Route::get('/ticket/cierre/{cashRegister}', [TicketController::class, 'cierre'])->name('ticket.cierre');
});
