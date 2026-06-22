<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Club VIP | Arte & Navaja</title>
    <link rel="icon" type="image/png" href="{{ asset('images/logo.png') }}">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style type="text/tailwindcss">
        :root {
            --gold: #c9a84c;
            --gold-light: #e8c96a;
            --dark: #080808;
            --card-bg: #111111;
            --border: #2a2a2a;
        }

        [x-cloak] { display: none !important; }

        body {
            background-color: var(--dark);
            color: #e8e8e8;
            font-family: 'Inter', sans-serif;
            min-height: 100dvh;
        }

        .font-display { font-family: 'Playfair Display', Georgia, serif; }

        .gold-gradient-text {
            background: linear-gradient(135deg, #c9a84c, #f0d060, #c9a84c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .btn-gold {
            background: linear-gradient(135deg, var(--gold), var(--gold-light));
            color: #080808;
            font-weight: 700;
            transition: all 0.2s;
            border: none;
            cursor: pointer;
        }
        .btn-gold:hover:not(:disabled) { opacity: 0.9; transform: translateY(-1px); }
        .btn-gold:active:not(:disabled) { transform: translateY(0); }
        .btn-gold:disabled { opacity: 0.45; cursor: not-allowed; }

        .field-input {
            background: #111;
            border: 1px solid var(--border);
            color: #e8e8e8;
            border-radius: 0.375rem;
            padding: 0.8rem 1rem;
            width: 100%;
            font-size: 0.95rem;
            transition: border-color 0.2s;
            box-sizing: border-box;
        }
        .field-input:focus { outline: none; border-color: var(--gold); }
        .field-input::placeholder { color: #4b5563; }
        .field-input.error { border-color: #ef4444; }

        input[type="date"].field-input::-webkit-calendar-picker-indicator {
            filter: invert(0.5);
        }

        .card-dark {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
        }

        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes fadeIn { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: translateY(0); } }
        .fade-in { animation: fadeIn 0.3s ease forwards; }
    </style>
</head>
<body>

{{-- ── Header ─────────────────────────────────────────────── --}}
<header style="border-bottom: 1px solid var(--border); padding: 1rem 1.5rem;
               display: flex; align-items: center; justify-content: space-between;">
    <a href="/" style="display: flex; align-items: center; gap: 0.75rem; text-decoration: none;">
        <div style="width: 36px; height: 36px; border-radius: 50%; overflow: hidden;
                    background: white; padding: 1px; flex-shrink: 0;">
            <img src="{{ asset('images/logo.jpeg') }}" alt="Arte & Navaja"
                 style="width: 100%; height: 100%; object-fit: contain;">
        </div>
        <span class="font-display" style="color: var(--gold); font-size: 1.1rem; font-weight: 700;">
            Arte & Navaja
        </span>
    </a>
    <a href="/"
       style="font-size: 0.72rem; color: #6b7280; text-decoration: none;
              letter-spacing: 0.1em; text-transform: uppercase;">
        ← Volver
    </a>
</header>

{{-- ── Main ─────────────────────────────────────────────────── --}}
<main style="min-height: calc(100dvh - 65px); display: flex; align-items: flex-start;
             justify-content: center; padding: 2.5rem 1rem 4rem;">

<div style="width: 100%; max-width: 28rem;" x-data="clubVIP()" x-cloak>

    {{-- ═══════════════════════════════════════════
         ESTADO: IDLE — Buscar por teléfono
    ═══════════════════════════════════════════ --}}
    <div x-show="state === 'idle'" class="fade-in">

        <div style="text-align: center; margin-bottom: 2rem;">
            <div style="display: inline-flex; align-items: center; gap: 0.5rem;
                        background: rgba(201,168,76,0.1); border: 1px solid rgba(201,168,76,0.25);
                        border-radius: 2rem; padding: 0.375rem 1rem; margin-bottom: 1.25rem;">
                <svg style="width:13px;height:13px;color:var(--gold);flex-shrink:0;" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" clip-rule="evenodd"/>
                </svg>
                <span style="font-size: 0.72rem; font-weight: 600; color: var(--gold);
                             letter-spacing: 0.12em; text-transform: uppercase;">Club VIP</span>
            </div>

            <h1 class="font-display gold-gradient-text"
                style="font-size: 2.1rem; font-weight: 900; line-height: 1.15; margin-bottom: 0.75rem;">
                Consultá tus<br>puntos VIP
            </h1>
            <p style="color: #6b7280; font-size: 0.875rem; line-height: 1.65;">
                Ingresá tu número de celular para ver tus puntos<br>acumulados o unirte al club.
            </p>
        </div>

        <div class="card-dark" style="padding: 1.75rem;">
            <label style="font-size: 0.7rem; font-weight: 600; color: #6b7280;
                          letter-spacing: 0.12em; text-transform: uppercase; display: block; margin-bottom: 0.5rem;">
                Número de celular
            </label>
            <input type="tel" x-model="phone" placeholder="Ej: 70123456"
                   @keydown.enter="buscar()"
                   :class="errors.phone ? 'field-input error' : 'field-input'"
                   style="font-size: 1.6rem; font-weight: 700; letter-spacing: 0.05em;
                          text-align: center; margin-bottom: 0.5rem;"/>
            <template x-if="errors.phone">
                <p style="color: #ef4444; font-size: 0.75rem; text-align: center; margin-bottom: 0.5rem;"
                   x-text="errors.phone[0]"></p>
            </template>

            <button @click="buscar()" class="btn-gold" :disabled="phone.length < 6"
                style="width: 100%; padding: 0.9rem; border-radius: 0.5rem;
                       font-size: 0.85rem; letter-spacing: 0.12em; text-transform: uppercase;
                       margin-top: 0.75rem;">
                Buscar mis puntos
            </button>
        </div>

        <p style="text-align: center; margin-top: 1.25rem; font-size: 0.72rem; color: #374151;">
            Sin contraseña · Solo tu número de celular
        </p>
    </div>

    {{-- ═══════════════════════════════════════════
         ESTADO: LOADING / REGISTERING — Spinner
    ═══════════════════════════════════════════ --}}
    <div x-show="state === 'loading' || state === 'registering'"
         style="text-align: center; padding: 4rem 1rem;">
        <div style="width: 48px; height: 48px; border-radius: 50%;
                    border: 3px solid var(--border); border-top-color: var(--gold);
                    animation: spin 0.75s linear infinite; margin: 0 auto 1.5rem;"></div>
        <p style="color: #6b7280; font-size: 0.875rem;"
           x-text="state === 'registering' ? 'Creando tu cuenta…' : 'Buscando tu perfil…'"></p>
    </div>

    {{-- ═══════════════════════════════════════════
         ESTADO: FOUND — Tarjeta de fidelidad
    ═══════════════════════════════════════════ --}}
    <div x-show="state === 'found'" class="fade-in">

        {{-- Banner: ya tenía cuenta --}}
        <div x-show="alreadyExisted" x-transition
             style="background: rgba(99,102,241,0.1); border: 1px solid rgba(99,102,241,0.3);
                    border-radius: 0.75rem; padding: 0.875rem 1.25rem; margin-bottom: 1rem;
                    display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.25rem; flex-shrink: 0;">ℹ️</span>
            <p style="font-size: 0.8rem; color: #a5b4fc;">
                Este número ya tenía una cuenta. ¡Aquí están tus puntos!
            </p>
        </div>

        {{-- Banner: recién registrado --}}
        <div x-show="justRegistered && !alreadyExisted" x-transition
             style="background: rgba(16,185,129,0.1); border: 1px solid rgba(16,185,129,0.3);
                    border-radius: 0.75rem; padding: 0.875rem 1.25rem; margin-bottom: 1rem;
                    display: flex; align-items: center; gap: 0.75rem;">
            <span style="font-size: 1.25rem; flex-shrink: 0;">🎉</span>
            <div>
                <p style="font-weight: 700; color: #34d399; font-size: 0.875rem; margin-bottom: 0.125rem;">
                    ¡Bienvenido al Club VIP!
                </p>
                <p style="font-size: 0.75rem; color: #6b7280;">
                    Mostrá esta pantalla cada vez que llegués a la barbería.
                </p>
            </div>
        </div>

        {{-- Banner: cumpleaños --}}
        <div x-show="customer?.is_birthday" x-transition
             style="background: rgba(251,191,36,0.1); border: 1px solid rgba(251,191,36,0.3);
                    border-radius: 0.75rem; padding: 0.875rem 1.25rem; margin-bottom: 1rem;
                    text-align: center;">
            <p style="font-size: 0.925rem; color: #fbbf24;">
                🎂 <strong>¡Feliz cumpleaños!</strong><br>
                <span style="font-size: 0.78rem; color: #d97706;">
                    Consultá con nosotros tu beneficio especial de hoy.
                </span>
            </p>
        </div>

        {{-- ── Tarjeta VIP ───────────────────────────────────── --}}
        <div style="background: linear-gradient(135deg, #181818, #242424);
                    border: 1px solid rgba(201,168,76,0.3); border-radius: 1.25rem;
                    padding: 1.75rem; margin-bottom: 1.25rem; position: relative; overflow: hidden;">

            <div style="position: absolute; top: -50px; right: -50px; width: 150px; height: 150px;
                        border-radius: 50%; background: radial-gradient(circle, rgba(201,168,76,0.12), transparent);
                        pointer-events: none;"></div>

            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.5rem;">
                <div>
                    <div style="font-size: 0.62rem; letter-spacing: 0.2em; color: #4b5563;
                                text-transform: uppercase; margin-bottom: 0.2rem;">Arte & Navaja</div>
                    <div class="font-display" style="color: var(--gold); font-size: 1rem; font-weight: 700;">Club VIP</div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.62rem; letter-spacing: 0.1em; color: #4b5563;
                                text-transform: uppercase; margin-bottom: 0.2rem;">Miembro</div>
                    <div style="font-size: 0.9rem; color: #e8e8e8; font-weight: 600;" x-text="customer?.name"></div>
                </div>
            </div>

            {{-- Puntos --}}
            <div style="margin-bottom: 1.25rem;">
                <div style="font-size: 0.62rem; letter-spacing: 0.2em; color: #4b5563;
                            text-transform: uppercase; margin-bottom: 0.2rem;">Puntos acumulados</div>
                <div class="font-display gold-gradient-text"
                     style="font-size: 5rem; font-weight: 900; line-height: 1;" x-text="customer?.loyalty_points ?? 0"></div>
                <div style="font-size: 0.72rem; color: #6b7280; margin-top: 0.25rem;"
                     x-text="'Visitas: ' + (customer?.total_visits ?? 0)"></div>
            </div>

            {{-- Barra progreso hacia próximo premio --}}
            <template x-if="nextReward">
                <div>
                    <div style="display: flex; justify-content: space-between;
                                font-size: 0.68rem; color: #6b7280; margin-bottom: 0.4rem;">
                        <span>Próximo: <span style="color: var(--gold);" x-text="nextReward.name"></span></span>
                        <span>
                            <span x-text="customer?.loyalty_points ?? 0"></span> /
                            <span x-text="nextReward.points_required"></span> pts
                        </span>
                    </div>
                    <div style="height: 5px; border-radius: 3px; background: var(--border);">
                        <div :style="'width:' + progressToNext + '%;height:100%;border-radius:3px;' +
                                     'background:linear-gradient(90deg,var(--gold),var(--gold-light));' +
                                     'transition:width 0.6s ease;'"></div>
                    </div>
                </div>
            </template>
            <template x-if="!nextReward && rewards.length > 0">
                <p style="font-size: 0.75rem; color: #34d399;">
                    ✓ Podés canjear todos los premios disponibles
                </p>
            </template>

            <div style="margin-top: 1.25rem; padding-top: 1rem; border-top: 1px solid var(--border);
                        display: flex; justify-content: space-between; align-items: flex-end;">
                <div>
                    <div style="font-size: 0.62rem; color: #374151; text-transform: uppercase;
                                letter-spacing: 0.1em; margin-bottom: 0.2rem;">Miembro desde</div>
                    <div style="font-size: 0.8rem; color: #6b7280;" x-text="customer?.member_since"></div>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 0.62rem; color: #374151; text-transform: uppercase;
                                letter-spacing: 0.08em; margin-bottom: 0.2rem;">Mostrá esta pantalla</div>
                    <div style="font-size: 0.72rem; color: var(--gold); font-weight: 600;">al llegar a la barbería</div>
                </div>
            </div>
        </div>

        {{-- ── Lista de premios ──────────────────────────────── --}}
        <template x-if="rewards.length > 0">
            <div class="card-dark" style="padding: 1.25rem; margin-bottom: 1.25rem;">
                <h3 style="font-size: 0.68rem; font-weight: 600; letter-spacing: 0.15em;
                           text-transform: uppercase; color: #6b7280; margin-bottom: 1rem;">
                    Premios del programa
                </h3>
                <div style="display: flex; flex-direction: column; gap: 0.625rem;">
                    <template x-for="(reward, i) in rewards" :key="i">
                        <div :style="reward.can_redeem
                            ? 'border:1px solid rgba(201,168,76,0.4);background:rgba(201,168,76,0.06);border-radius:.625rem;padding:.7rem 1rem;display:flex;align-items:center;gap:.75rem;'
                            : 'border:1px solid var(--border);border-radius:.625rem;padding:.7rem 1rem;display:flex;align-items:center;gap:.75rem;opacity:.6;'">
                            <div style="flex:1;min-width:0;">
                                <p style="font-size:.875rem;font-weight:600;color:#e8e8e8;margin-bottom:.1rem;"
                                   x-text="reward.name"></p>
                                <p style="font-size:.7rem;color:#6b7280;"
                                   x-text="reward.points_required + ' puntos requeridos'"></p>
                            </div>
                            <template x-if="reward.can_redeem">
                                <span style="font-size:.68rem;font-weight:700;background:var(--gold);color:#080808;
                                             padding:.25rem .625rem;border-radius:.375rem;white-space:nowrap;flex-shrink:0;">
                                    DISPONIBLE
                                </span>
                            </template>
                            <template x-if="!reward.can_redeem">
                                <span style="font-size:.68rem;color:#4b5563;white-space:nowrap;flex-shrink:0;"
                                      x-text="'Faltan ' + (reward.points_required - (customer?.loyalty_points ?? 0)) + ' pts'"></span>
                            </template>
                        </div>
                    </template>
                </div>
                <p style="font-size:.68rem;color:#374151;margin-top:.875rem;text-align:center;">
                    Los premios se canjean en la barbería con el cajero.
                </p>
            </div>
        </template>

        <button @click="reset()"
            style="width:100%;padding:.7rem;background:transparent;
                   border:1px solid var(--border);color:#6b7280;border-radius:.5rem;
                   cursor:pointer;font-size:.78rem;letter-spacing:.1em;text-transform:uppercase;">
            Buscar otro número
        </button>
    </div>

    {{-- ═══════════════════════════════════════════
         ESTADO: NOT FOUND — Formulario de registro
    ═══════════════════════════════════════════ --}}
    <div x-show="state === 'not_found'" class="fade-in">

        <div style="text-align:center;margin-bottom:1.75rem;">
            <div style="font-size:2rem;margin-bottom:.75rem;">💈</div>
            <h2 class="font-display" style="color:white;font-size:1.5rem;font-weight:700;margin-bottom:.5rem;">
                ¡Registrarte es gratis!
            </h2>
            <p style="color:#6b7280;font-size:.825rem;line-height:1.65;">
                El número <strong style="color:#9ca3af;" x-text="phone"></strong> no está registrado.<br>
                Completá los datos para unirte al Club VIP.
            </p>
        </div>

        <div class="card-dark" style="padding:1.75rem;">

            {{-- Nombre --}}
            <div style="margin-bottom:1rem;">
                <label style="font-size:.7rem;font-weight:600;color:#6b7280;letter-spacing:.1em;
                              text-transform:uppercase;display:block;margin-bottom:.4rem;">
                    Nombre completo *
                </label>
                <input type="text" x-model="regName" placeholder="Tu nombre completo"
                       :class="errors.name ? 'field-input error' : 'field-input'"
                       autocomplete="name"/>
                <template x-if="errors.name">
                    <p style="color:#ef4444;font-size:.72rem;margin-top:.25rem;" x-text="errors.name[0]"></p>
                </template>
            </div>

            {{-- Teléfono --}}
            <div style="margin-bottom:1rem;">
                <label style="font-size:.7rem;font-weight:600;color:#6b7280;letter-spacing:.1em;
                              text-transform:uppercase;display:block;margin-bottom:.4rem;">
                    Número de celular *
                </label>
                <input type="tel" x-model="phone" placeholder="70123456"
                       :class="errors.phone ? 'field-input error' : 'field-input'"
                       autocomplete="tel"/>
                <template x-if="errors.phone">
                    <p style="color:#ef4444;font-size:.72rem;margin-top:.25rem;" x-text="errors.phone[0]"></p>
                </template>
            </div>

            {{-- Carnet de identidad --}}
            <div style="margin-bottom:1rem;">
                <label style="font-size:.7rem;font-weight:600;color:#6b7280;letter-spacing:.1em;
                              text-transform:uppercase;display:block;margin-bottom:.4rem;">
                    Carnet de identidad *
                </label>
                <input type="text" x-model="regCi" placeholder="Ej: 12345678"
                       :class="errors.ci ? 'field-input error' : 'field-input'"
                       autocomplete="off"/>
                <template x-if="errors.ci">
                    <p style="color:#ef4444;font-size:.72rem;margin-top:.25rem;" x-text="errors.ci[0]"></p>
                </template>
            </div>

            {{-- Fecha de nacimiento --}}
            <div style="margin-bottom:1.5rem;">
                <label style="font-size:.7rem;font-weight:600;color:#6b7280;letter-spacing:.1em;
                              text-transform:uppercase;display:block;margin-bottom:.4rem;">
                    Fecha de nacimiento *
                </label>
                <input type="date" x-model="regBirthDate"
                       :class="errors.birth_date ? 'field-input error' : 'field-input'"
                       autocomplete="bday"/>
                <template x-if="errors.birth_date">
                    <p style="color:#ef4444;font-size:.72rem;margin-top:.25rem;" x-text="errors.birth_date[0]"></p>
                </template>
                <p style="font-size:.68rem;color:#374151;margin-top:.3rem;">
                    Usamos tu fecha de nacimiento para tu beneficio de cumpleaños.
                </p>
            </div>

            <button @click="registrar()" class="btn-gold"
                :disabled="!regName.trim() || phone.length < 6 || !regCi.trim() || !regBirthDate"
                style="width:100%;padding:.9rem;border-radius:.5rem;
                       font-size:.85rem;letter-spacing:.12em;text-transform:uppercase;
                       margin-bottom:.75rem;">
                Unirme al Club VIP
            </button>

            <button @click="state = 'idle'; errors = {};"
                style="width:100%;padding:.625rem;background:transparent;border:none;
                       color:#6b7280;cursor:pointer;font-size:.78rem;">
                ← Volver
            </button>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════
         ESTADO: ERROR
    ═══════════════════════════════════════════ --}}
    <div x-show="state === 'error'"
         style="text-align:center;padding:3rem 1rem;">
        <div style="font-size:2.5rem;margin-bottom:1rem;">⚠️</div>
        <p style="color:#f87171;margin-bottom:1.5rem;font-size:.9rem;">
            Ocurrió un error. Por favor intentá de nuevo.
        </p>
        <button @click="reset()" class="btn-gold"
            style="padding:.75rem 2rem;border-radius:.5rem;font-size:.875rem;">
            Reintentar
        </button>
    </div>

</div>
</main>

<script>
function clubVIP() {
    return {
        state: 'idle',
        phone: '',
        regName: '',
        regCi: '',
        regBirthDate: '',
        customer: null,
        rewards: [],
        errors: {},
        justRegistered: false,
        alreadyExisted: false,

        get nextReward() {
            return this.rewards.find(r => !r.can_redeem) ?? null;
        },

        get progressToNext() {
            if (!this.nextReward) return 100;
            const pts = this.customer?.loyalty_points ?? 0;
            return Math.min(100, Math.round(pts / this.nextReward.points_required * 100));
        },

        reset() {
            this.state          = 'idle';
            this.phone          = '';
            this.customer       = null;
            this.rewards        = [];
            this.errors         = {};
            this.justRegistered = false;
            this.alreadyExisted = false;
        },

        csrfToken() {
            return document.querySelector('meta[name="csrf-token"]')?.content ?? '';
        },

        async buscar() {
            if (this.phone.length < 6) return;
            this.state  = 'loading';
            this.errors = {};
            try {
                const res  = await fetch('/fidelizacion/buscar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({ phone: this.phone }),
                });
                const data = await res.json();

                if (res.status === 422) { this.errors = data.errors ?? {}; this.state = 'idle'; return; }
                if (!res.ok)            { this.state = 'error'; return; }

                if (data.found) {
                    this.customer       = data.customer;
                    this.rewards        = data.rewards;
                    this.justRegistered = false;
                    this.alreadyExisted = false;
                    this.state          = 'found';
                } else {
                    this.state = 'not_found';
                }
            } catch {
                this.state = 'error';
            }
        },

        async registrar() {
            if (!this.regName.trim() || !this.regCi.trim() || !this.regBirthDate) return;
            this.state  = 'registering';
            this.errors = {};
            try {
                const res  = await fetch('/fidelizacion/registrar', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept':       'application/json',
                        'X-CSRF-TOKEN': this.csrfToken(),
                    },
                    body: JSON.stringify({
                        name:       this.regName.trim(),
                        phone:      this.phone,
                        ci:         this.regCi.trim(),
                        birth_date: this.regBirthDate,
                    }),
                });
                const data = await res.json();

                if (res.status === 422) { this.errors = data.errors ?? {}; this.state = 'not_found'; return; }
                if (!res.ok || !data.success) { this.state = 'error'; return; }

                this.customer       = data.customer;
                this.rewards        = data.rewards;
                this.justRegistered = true;
                this.alreadyExisted = data.already_existed ?? false;
                this.state          = 'found';
            } catch {
                this.state = 'error';
            }
        },
    };
}
</script>

</body>
</html>
