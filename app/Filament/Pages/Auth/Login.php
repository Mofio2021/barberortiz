<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Filament\Facades\Filament;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Support\Facades\Hash;

class Login extends BaseLogin
{
    // Controla si el formulario muestra PIN o contraseña
    public bool $pinMode = false;

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent(),

                // Campo contraseña — visible solo en modo estándar
                $this->getPasswordFormComponent()
                    ->required(fn (): bool => ! $this->pinMode)
                    ->hidden(fn (): bool => $this->pinMode),

                // Campo PIN — visible solo en modo PIN
                Forms\Components\TextInput::make('pin')
                    ->label('PIN de acceso (4 dígitos)')
                    ->numeric()
                    ->length(4)
                    ->password()
                    ->revealable()
                    ->hidden(fn (): bool => ! $this->pinMode),

                // Botón de toggle entre modos
                Forms\Components\Actions::make([
                    Forms\Components\Actions\Action::make('toggle_pin_mode')
                        ->label(fn (): string => $this->pinMode
                            ? '← Usar contraseña larga'
                            : 'Acceso rápido con PIN →')
                        ->color('warning')
                        ->link()
                        ->action('toggleMode'),
                ])->alignCenter(),
            ]);
    }

    // Alterna entre modo PIN y modo contraseña
    public function toggleMode(): void
    {
        $this->pinMode = ! $this->pinMode;
    }

    public function authenticate(): void
    {
        $data = $this->form->getState();

        // Flujo de autenticación con PIN
        if ($this->pinMode && filled($data['pin'] ?? null)) {
            $user = User::where('email', $data['email'])->first();

            if (! $user || ! $user->pin || ! Hash::check((string) $data['pin'], $user->pin)) {
                $this->throwFailureValidationException();
            }

            Filament::auth()->login($user, remember: true);
            session()->regenerate();
            $this->redirect(Filament::getUrl());

            return;
        }

        // Flujo estándar de contraseña
        parent::authenticate();
    }
}
