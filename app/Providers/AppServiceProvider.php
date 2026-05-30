<?php

namespace App\Providers;

use App\Filament\Pages\Auth\Login;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Registrar el componente de Login personalizado con Livewire en cada request.
        // Sin esto, las peticiones AJAX de /livewire/update fallan porque no pasan
        // por el middleware del panel (que es quien normalmente hace este registro).
        Livewire::component('app.filament.pages.auth.login', Login::class);
    }
}
