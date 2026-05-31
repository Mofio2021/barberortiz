<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/ticket/venta/{sale}',         [TicketController::class, 'venta'])->name('ticket.venta');
    Route::get('/ticket/cierre/{cashRegister}', [TicketController::class, 'cierre'])->name('ticket.cierre');
});
