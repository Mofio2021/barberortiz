<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TicketController;
use App\Models\Branch;
use App\Models\Service;

Route::get('/', function () {
    $branches = Branch::where('is_active', true)
        ->with([
            'staff' => fn ($q) => $q->where('status', 'active')->orderBy('name'),
            'galleryPhotos' => fn ($q) => $q->where('is_active', true)->orderBy('sort_order'),
        ])
        ->get();

    // Servicios por sucursal: globales (branch_id null) + los propios de cada sucursal
    $servicesByBranch = $branches->mapWithKeys(fn ($branch) =>
        [$branch->id => Service::active()
            ->forBranch($branch->id)
            ->orderBy('name')
            ->get()
            ->map(fn ($s) => [
                'id'          => $s->id,
                'name'        => $s->name,
                'description' => $s->description,
                'price'       => $s->price,
            ])->values()]
    );

    return view('landing', compact('branches', 'servicesByBranch'));
});

Route::get('/login', fn() => redirect()->route('filament.admin.auth.login'))->name('login');

Route::middleware(['auth'])->group(function () {
    Route::get('/ticket/venta/{sale}',         [TicketController::class, 'venta'])->name('ticket.venta');
    Route::get('/ticket/cierre/{cashRegister}', [TicketController::class, 'cierre'])->name('ticket.cierre');
});
