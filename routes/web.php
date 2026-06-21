<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Models\Branch;
use App\Models\Service;

Route::get('/', function () {
    $services = Service::active()->orderBy('name')->get();

    $branches = Branch::where('is_active', true)
        ->with([
            'staff' => fn ($q) => $q->where('status', 'active')->orderBy('name'),
            'galleryPhotos' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ])
        ->get();

    return view('landing', compact('services', 'branches'));
});

Route::get('/login', fn() => redirect()->route('filament.admin.auth.login'))->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/ticket/venta/{sale}',         [TicketController::class, 'venta'])->name('ticket.venta');
    Route::get('/ticket/cierre/{cashRegister}', [TicketController::class, 'cierre'])->name('ticket.cierre');
});
