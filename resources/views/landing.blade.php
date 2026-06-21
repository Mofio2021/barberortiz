<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Arte & Navaja — Barbería Premium. Cortes clásicos y modernos con la mejor atención.">
    <title>Arte & Navaja | Barbería Premium</title>
    <link rel="icon" type="image/jpeg" href="{{ asset('images/logo.jpeg') }}">

    <script src="https://cdn.jsdelivr.net/npm/@tailwindcss/browser@4"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;0,900;1,400&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style type="text/tailwindcss">
        :root {
            --gold: #c9a84c;
            --gold-light: #e8c96a;
            --dark: #080808;
            --card-bg: #111111;
            --border: #2a2a2a;
        }

        html { scroll-behavior: smooth; }
        [x-cloak] { display: none !important; }

        body {
            background-color: var(--dark);
            color: #e8e8e8;
            font-family: 'Inter', sans-serif;
        }

        ::-webkit-scrollbar { width: 5px; }
        ::-webkit-scrollbar-track { background: var(--dark); }
        ::-webkit-scrollbar-thumb { background: var(--gold); border-radius: 3px; }

        .font-display { font-family: 'Playfair Display', Georgia, serif; }

        .gold-gradient-text {
            background: linear-gradient(135deg, #c9a84c, #f0d060, #c9a84c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .gold-line {
            width: 50px;
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
        }

        .navbar-blur {
            backdrop-filter: blur(20px);
            background-color: rgba(8, 8, 8, 0.88);
            border-bottom: 1px solid rgba(201, 168, 76, 0.12);
        }

        .hero-bg {
            background:
                radial-gradient(ellipse at 75% 40%, rgba(201, 168, 76, 0.07) 0%, transparent 55%),
                radial-gradient(ellipse at 15% 85%, rgba(201, 168, 76, 0.04) 0%, transparent 45%),
                #080808;
        }

        .btn-gold {
            background-color: var(--gold);
            color: #080808;
            font-weight: 600;
            letter-spacing: 0.08em;
            transition: all 0.3s ease;
        }
        .btn-gold:hover {
            background-color: var(--gold-light);
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(201, 168, 76, 0.25);
        }

        .btn-outline {
            border: 1px solid var(--gold);
            color: var(--gold);
            background: transparent;
            letter-spacing: 0.08em;
            transition: all 0.3s ease;
        }
        .btn-outline:hover {
            background-color: var(--gold);
            color: #080808;
            transform: translateY(-2px);
        }

        .card-dark {
            background-color: var(--card-bg);
            border: 1px solid var(--border);
            transition: border-color 0.3s ease, transform 0.3s ease;
        }
        .card-dark:hover {
            border-color: rgba(201, 168, 76, 0.35);
            transform: translateY(-4px);
        }

        .gallery-item { overflow: hidden; }
        .gallery-item img { transition: transform 0.5s ease, opacity 0.3s ease; opacity: 0.75; }
        .gallery-item:hover img { transform: scale(1.07); opacity: 1; }

        #mobile-menu { display: none; }
        #mobile-menu.open { display: block; }

        /* Branch selector overlay */
        .branch-overlay {
            position: fixed;
            inset: 0;
            z-index: 100;
            background: rgba(8,8,8,0.97);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .branch-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 1rem;
            padding: 2.5rem 2rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            width: 200px;
        }
        .branch-card:hover {
            border-color: var(--gold);
            transform: translateY(-6px);
            box-shadow: 0 20px 40px rgba(201,168,76,0.15);
        }

        /* Barber card */
        .barber-card {
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: 0.75rem;
            overflow: hidden;
            text-align: center;
            transition: transform 0.3s ease, border-color 0.3s ease;
        }
        .barber-card:hover {
            transform: translateY(-4px);
            border-color: rgba(201,168,76,0.4);
        }
        .barber-avatar {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--gold);
            margin: 0 auto;
        }
        .barber-avatar-placeholder {
            width: 96px;
            height: 96px;
            border-radius: 50%;
            background: rgba(201,168,76,0.1);
            border: 3px solid var(--gold);
            margin: 0 auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(28px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .anim { animation: fadeInUp 0.8s ease forwards; }
        .anim-d1 { animation-delay: 0.1s; opacity: 0; }
        .anim-d2 { animation-delay: 0.25s; opacity: 0; }
        .anim-d3 { animation-delay: 0.4s; opacity: 0; }
    </style>
</head>
<body x-data="branchApp()" x-init="init()">

@php
    $branchesJson = $branches->map(fn($b) => [
        'id'       => $b->id,
        'name'     => $b->name,
        'staff'    => $b->staff->map(fn($s) => [
            'id'     => $s->id,
            'name'   => $s->name,
            'role'   => $s->role,
            'avatar' => $s->avatar ? asset('storage/'.$s->avatar) : null,
        ])->values(),
        'photos'   => $b->galleryPhotos->map(fn($p) => [
            'id'      => $p->id,
            'url'     => asset('storage/'.$p->image_path),
            'caption' => $p->caption,
        ])->values(),
        'services' => $servicesByBranch[$b->id] ?? collect(),
    ])->values()->toJson();
@endphp

<script>
function branchApp() {
    return {
        branches: @json($branches->map(fn($b) => ['id'=>$b->id,'name'=>$b->name])->values()),
        allData:  {!! $branchesJson !!},
        selectedId: null,
        showSelector: false,

        init() {
            const saved = localStorage.getItem('arte_navaja_branch');
            if (saved) {
                this.selectedId = parseInt(saved);
            } else {
                this.showSelector = true;
            }
        },

        selectBranch(id) {
            this.selectedId = id;
            localStorage.setItem('arte_navaja_branch', id);
            this.showSelector = false;
        },

        changeBranch() {
            this.showSelector = true;
        },

        get currentBranch() {
            return this.allData.find(b => b.id === this.selectedId) || null;
        },

        get currentStaff() {
            return this.currentBranch ? this.currentBranch.staff : [];
        },

        get currentPhotos() {
            return this.currentBranch ? this.currentBranch.photos : [];
        },

        get currentBranchName() {
            return this.currentBranch ? this.currentBranch.name : '';
        },

        get currentServices() {
            return this.currentBranch ? this.currentBranch.services : [];
        },

        serviceIcons: [
            'M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.243-4.243 3 3 0 004.243 4.243z',
            'M5 3l14 9-14 9V3z',
            'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z',
            'M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z',
            'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01',
            'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z',
        ],
    }
}
</script>

<!-- ================================================
     SELECTOR DE SUCURSAL (overlay)
================================================ -->
<div x-show="showSelector" x-cloak class="branch-overlay">
    <div style="max-width:560px; width:100%; padding:2rem">
        <div class="text-center mb-10">
            <div class="w-20 h-20 rounded-full overflow-hidden bg-white mx-auto mb-5" style="padding:2px">
                <img src="{{ asset('images/logo.jpeg') }}" alt="Arte & Navaja" class="w-full h-full object-contain">
            </div>
            <h2 class="font-display text-3xl md:text-4xl font-bold text-white mb-3">¿Cuál sucursal te queda más cerca?</h2>
            <p class="text-gray-600 text-sm">Elegí y personalizamos tu experiencia.</p>
        </div>

        <div class="flex flex-col sm:flex-row gap-6 justify-center">
            @foreach($branches as $branch)
            <div class="branch-card" @click="selectBranch({{ $branch->id }})">
                <div class="w-16 h-16 mx-auto mb-4 rounded-full flex items-center justify-center"
                     style="background: rgba(201,168,76,0.12)">
                    <svg class="w-8 h-8" style="color:var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="font-display text-lg font-bold text-white mb-1">{{ $branch->name }}</div>
                @if($branch->address)
                <div class="text-gray-600 text-xs">{{ $branch->address }}</div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
</div>

<!-- ================================================
     NAVBAR
================================================ -->
<nav class="fixed top-0 left-0 right-0 z-50 navbar-blur">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="flex items-center justify-between h-16">

            <a href="#" class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full overflow-hidden bg-white flex-shrink-0 flex items-center justify-center" style="padding:1px">
                    <img src="{{ asset('images/logo.jpeg') }}" alt="Arte & Navaja" class="w-full h-full object-contain">
                </div>
                <span class="font-display text-xl font-bold" style="color: var(--gold)">Arte & Navaja</span>
            </a>

            <div class="hidden md:flex items-center gap-8">
                <a href="#servicios"   class="text-xs text-gray-400 hover:text-white transition-colors tracking-[0.15em] uppercase">Servicios</a>
                <a href="#barberos"   class="text-xs text-gray-400 hover:text-white transition-colors tracking-[0.15em] uppercase">Barberos</a>
                <a href="#galeria"     class="text-xs text-gray-400 hover:text-white transition-colors tracking-[0.15em] uppercase">Galería</a>
                <a href="#testimonios" class="text-xs text-gray-400 hover:text-white transition-colors tracking-[0.15em] uppercase">Opiniones</a>
                <a href="#fidelizacion" class="text-xs text-gray-400 hover:text-white transition-colors tracking-[0.15em] uppercase">Club VIP</a>
                <button @click="changeBranch()"
                        x-show="selectedId"
                        class="btn-outline px-3 py-2 rounded-sm text-xs uppercase tracking-widest flex items-center gap-2">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span x-text="currentBranchName"></span>
                </button>
                <a href="{{ url('/admin') }}"
                   class="btn-gold px-5 py-2 rounded-sm text-xs uppercase tracking-widest">
                    Ingresar
                </a>
            </div>

            <button class="md:hidden p-2" onclick="document.getElementById('mobile-menu').classList.toggle('open')">
                <svg class="w-6 h-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>
        </div>

        <div id="mobile-menu" class="md:hidden pb-5 pt-4 border-t" style="border-color: var(--border)">
            <div class="flex flex-col gap-4">
                <a href="#servicios"    onclick="document.getElementById('mobile-menu').classList.remove('open')" class="text-xs text-gray-400 tracking-widest uppercase">Servicios</a>
                <a href="#galeria"      onclick="document.getElementById('mobile-menu').classList.remove('open')" class="text-xs text-gray-400 tracking-widest uppercase">Galería</a>
                <a href="#testimonios"  onclick="document.getElementById('mobile-menu').classList.remove('open')" class="text-xs text-gray-400 tracking-widest uppercase">Opiniones</a>
                <a href="#fidelizacion" onclick="document.getElementById('mobile-menu').classList.remove('open')" class="text-xs text-gray-400 tracking-widest uppercase">Club VIP</a>
                <button x-show="selectedId"
                        @click="changeBranch(); document.getElementById('mobile-menu').classList.remove('open')"
                        class="btn-outline px-4 py-2.5 rounded-sm text-xs uppercase tracking-widest flex items-center gap-2 w-full justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    </svg>
                    <span x-text="currentBranchName"></span>
                </button>
                <a href="{{ url('/admin') }}"
                   class="btn-gold px-5 py-2.5 rounded-sm text-xs uppercase tracking-widest text-center">
                    Ingresar
                </a>
            </div>
        </div>
    </div>
</nav>

<!-- ================================================
     HERO
================================================ -->
<section id="inicio" class="hero-bg min-h-screen flex items-center relative overflow-hidden">

    <div class="absolute top-1/3 right-0 w-px h-48 opacity-15 pointer-events-none"
         style="background: linear-gradient(180deg, transparent, var(--gold), transparent)"></div>
    <div class="absolute bottom-1/4 left-0 w-px h-48 opacity-15 pointer-events-none"
         style="background: linear-gradient(180deg, transparent, var(--gold), transparent)"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-36 md:py-0 w-full">
        <div class="max-w-2xl">

            <h1 class="font-display font-black leading-none mb-6 anim anim-d2"
                style="font-size: clamp(3.5rem, 10vw, 7rem)">
                <span class="gold-gradient-text">Arte</span><span class="text-white"> &</span><br>
                <span class="text-white">Navaja</span>
            </h1>

            <p class="text-lg text-gray-400 font-light mb-2 anim anim-d2">
                Donde el estilo se convierte en arte.
            </p>
            <p class="text-sm text-gray-600 mb-10 max-w-md leading-relaxed anim anim-d2">
                Cortes precisos, barba definida y el arte del afeitado clásico.
                Tu estilo, nuestra firma.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 anim anim-d3">
                <a href="#servicios"
                   class="btn-gold px-8 py-3.5 rounded-sm text-xs uppercase tracking-[0.18em] font-bold text-center">
                    Ver servicios
                </a>
                <a href="#fidelizacion"
                   class="btn-outline px-8 py-3.5 rounded-sm text-xs uppercase tracking-[0.18em] font-medium text-center">
                    Club de lealtad
                </a>
            </div>

            <div class="flex gap-10 mt-16 pt-10 border-t anim anim-d3" style="border-color: var(--border)">
                <div>
                    <div class="font-display text-3xl font-bold" style="color: var(--gold)">8+</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wider mt-1">Años de experiencia</div>
                </div>
                <div>
                    <div class="font-display text-3xl font-bold" style="color: var(--gold)">500+</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wider mt-1">Clientes satisfechos</div>
                </div>
                <div>
                    <div class="font-display text-3xl font-bold" style="color: var(--gold)">100%</div>
                    <div class="text-xs text-gray-600 uppercase tracking-wider mt-1">Dedicación</div>
                </div>
            </div>
        </div>
    </div>

    <div class="absolute bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-bounce pointer-events-none">
        <div class="w-px h-8" style="background: linear-gradient(180deg, var(--gold), transparent)"></div>
    </div>
</section>

<!-- ================================================
     SERVICIOS
================================================ -->
<section id="servicios" class="py-24" style="background-color: #0d0d0d">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">

        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="gold-line"></div>
                <span class="text-xs tracking-[0.3em] uppercase" style="color: var(--gold)">Lo que hacemos</span>
                <div class="gold-line"></div>
            </div>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Nuestros Servicios</h2>
        </div>

        {{-- Servicios dinámicos según sucursal seleccionada --}}
        <div x-show="currentServices.length === 0">
            <p class="text-center text-gray-600 text-sm">Próximamente publicaremos nuestros servicios.</p>
        </div>

        <div x-show="currentServices.length > 0"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            <template x-for="(service, index) in currentServices" :key="service.id">
                <div class="card-dark rounded-lg p-8 text-center"
                     :style="index === 3 ? 'border-color: rgba(201,168,76,0.25)' : ''">
                    <div class="w-14 h-14 mx-auto mb-6 rounded-full flex items-center justify-center"
                         :style="'background: rgba(201,168,76,' + (index === 3 ? '0.15' : '0.1') + ')'">
                        <svg class="w-7 h-7" style="color: var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                  :d="serviceIcons[index % serviceIcons.length]"/>
                        </svg>
                    </div>
                    <h3 class="font-display text-xl font-bold text-white mb-3" x-text="service.name"></h3>
                    <p class="text-gray-500 text-sm leading-relaxed mb-5" x-text="service.description || ' '"></p>
                    <div class="font-display text-2xl font-bold" style="color: var(--gold)"
                         x-text="'Bs ' + Number(service.price).toLocaleString('es-BO', {maximumFractionDigits: 0})">
                    </div>
                </div>
            </template>
        </div>
    </div>
</section>

<!-- ================================================
     BARBEROS
================================================ -->
<section id="barberos" class="py-24" style="background-color: var(--dark)">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">

        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="gold-line"></div>
                <span class="text-xs tracking-[0.3em] uppercase" style="color: var(--gold)">Nuestro equipo</span>
                <div class="gold-line"></div>
            </div>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-white">
                Los Barberos de <span x-text="currentBranchName" class="gold-gradient-text"></span>
            </h2>
        </div>

        <div x-show="currentStaff.length > 0"
             class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-6">
            <template x-for="barber in currentStaff" :key="barber.id">
                <div class="barber-card p-6">
                    <div class="mb-4">
                        <template x-if="barber.avatar">
                            <img :src="barber.avatar" :alt="barber.name" class="barber-avatar">
                        </template>
                        <template x-if="!barber.avatar">
                            <div class="barber-avatar-placeholder">
                                <svg class="w-10 h-10" style="color:var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </div>
                        </template>
                    </div>
                    <div class="font-display text-lg font-bold text-white mb-1" x-text="barber.name"></div>
                    <div class="text-xs uppercase tracking-wider" style="color:var(--gold)" x-text="barber.role === 'barbero' ? 'Barbero' : 'Estilista'"></div>
                </div>
            </template>
        </div>

        <div x-show="currentStaff.length === 0" class="text-center text-gray-600 text-sm">
            Seleccioná una sucursal para ver el equipo.
        </div>

    </div>
</section>

<!-- ================================================
     GALERÍA
================================================ -->
<section id="galeria" class="py-24" style="background-color: #0d0d0d">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">

        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="gold-line"></div>
                <span class="text-xs tracking-[0.3em] uppercase" style="color: var(--gold)">Nuestro trabajo</span>
                <div class="gold-line"></div>
            </div>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Galería</h2>
            <p class="text-gray-600 text-sm mt-4 max-w-md mx-auto">
                Cada corte cuenta una historia. Trabajos reales de nuestros barberos.
            </p>
        </div>

        {{-- Fotos reales desde DB, filtradas por sucursal --}}
        <div x-show="currentPhotos.length > 0"
             class="grid grid-cols-2 md:grid-cols-3 gap-3">
            <template x-for="(photo, index) in currentPhotos" :key="photo.id">
                <div class="gallery-item rounded-lg overflow-hidden"
                     :class="index === currentPhotos.length - 1 && currentPhotos.length % 2 !== 0 ? 'col-span-2 md:col-span-1' : ''"
                     style="aspect-ratio:1/1; background:#161616; border:1px solid var(--border)">
                    <img :src="photo.url" :alt="photo.caption || 'Trabajo de barbería'"
                         class="w-full h-full object-cover">
                </div>
            </template>
        </div>

        <div x-show="currentPhotos.length === 0"
             class="text-center py-16" style="border: 1px dashed var(--border); border-radius: 0.75rem">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-20" style="color:var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                      d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
            <p class="text-gray-700 text-sm">Las fotos aparecerán aquí una vez que los barberos las suban desde el panel.</p>
        </div>

        <p class="text-center text-gray-700 text-xs mt-8">
            ¿Querés ver más?
            <a href="#" style="color: var(--gold)" class="ml-1 hover:underline">Seguinos en Instagram</a>
        </p>
    </div>
</section>

<!-- ================================================
     TESTIMONIOS
================================================ -->
<section id="testimonios" class="py-24" style="background-color: #0d0d0d">
    <div class="max-w-7xl mx-auto px-6 lg:px-10">

        <div class="text-center mb-16">
            <div class="flex items-center justify-center gap-4 mb-4">
                <div class="gold-line"></div>
                <span class="text-xs tracking-[0.3em] uppercase" style="color: var(--gold)">Lo que dicen</span>
                <div class="gold-line"></div>
            </div>
            <h2 class="font-display text-4xl md:text-5xl font-bold text-white">Nuestros Clientes</h2>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">

            <div class="card-dark rounded-lg p-8 relative">
                <div class="absolute top-6 right-8 font-display text-5xl font-black opacity-8 leading-none" style="color: var(--gold)">"</div>
                <div class="text-lg mb-4" style="color: var(--gold)">★★★★★</div>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                    "El mejor corte que me hice en años. Los chicos de Arte & Navaja entienden exactamente
                    lo que querés sin que tengas que explicar demasiado. Salí otro."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full overflow-hidden flex-shrink-0"
                         style="border: 2px solid var(--gold)">
                        <img src="https://picsum.photos/seed/p1/100/100" alt="Matías R." class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="text-white text-sm font-semibold">Matías R.</div>
                        <div class="text-gray-600 text-xs">Cliente habitual</div>
                    </div>
                </div>
            </div>

            <div class="card-dark rounded-lg p-8 relative" style="border-color: rgba(201,168,76,0.3)">
                <div class="absolute top-6 right-8 font-display text-5xl font-black opacity-8 leading-none" style="color: var(--gold)">"</div>
                <div class="text-lg mb-4" style="color: var(--gold)">★★★★★</div>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                    "El club de lealtad es genial, ya me gané un corte gratis. Pero más allá de eso,
                    el ambiente es increíble. Se nota que les apasiona lo que hacen."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full overflow-hidden flex-shrink-0"
                         style="border: 2px solid var(--gold)">
                        <img src="https://picsum.photos/seed/p2/100/100" alt="Carlos M." class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="text-white text-sm font-semibold">Carlos M.</div>
                        <div class="text-gray-600 text-xs">Miembro VIP</div>
                    </div>
                </div>
            </div>

            <div class="card-dark rounded-lg p-8 relative">
                <div class="absolute top-6 right-8 font-display text-5xl font-black opacity-8 leading-none" style="color: var(--gold)">"</div>
                <div class="text-lg mb-4" style="color: var(--gold)">★★★★★</div>
                <p class="text-gray-400 text-sm leading-relaxed mb-6">
                    "Llevo a mi hijo hace dos años y nunca fallaron. El afeitado a navaja es una
                    experiencia totalmente diferente. Los recomiendo a todos mis amigos."
                </p>
                <div class="flex items-center gap-3">
                    <div class="w-11 h-11 rounded-full overflow-hidden flex-shrink-0"
                         style="border: 2px solid var(--gold)">
                        <img src="https://picsum.photos/seed/p3/100/100" alt="Eduardo P." class="w-full h-full object-cover">
                    </div>
                    <div>
                        <div class="text-white text-sm font-semibold">Eduardo P.</div>
                        <div class="text-gray-600 text-xs">2 años de cliente</div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================================================
     CLUB VIP / FIDELIZACIÓN
================================================ -->
<section id="fidelizacion" class="py-24 relative overflow-hidden" style="background-color: var(--dark)">

    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] rounded-full opacity-[0.04] pointer-events-none"
         style="background: radial-gradient(circle, var(--gold), transparent)"></div>

    <div class="max-w-7xl mx-auto px-6 lg:px-10">
        <div class="grid md:grid-cols-2 gap-16 items-center">

            {{-- Info --}}
            <div>
                <div class="flex items-center gap-4 mb-6">
                    <div class="gold-line"></div>
                    <span class="text-xs tracking-[0.3em] uppercase" style="color: var(--gold)">Club VIP</span>
                </div>

                <h2 class="font-display text-4xl md:text-5xl font-bold text-white mb-6 leading-tight">
                    Programa de<br><span class="gold-gradient-text">Lealtad</span>
                </h2>

                <p class="text-gray-500 text-sm leading-relaxed mb-8">
                    Cada corte suma puntos. Los puntos se convierten en premios.
                    Únete al club y empezá a disfrutar de beneficios exclusivos desde el primer día.
                </p>

                <ul class="space-y-4 mb-10">
                    @foreach ([
                        '1 corte = 1 punto acumulado',
                        'Premio gratis al cumplir el puntaje objetivo',
                        'Bebida de cortesía al registrarte',
                        'Corte gratis en tu cumpleaños',
                        'Consultá tu saldo con tu número de celular, sin contraseña',
                    ] as $benefit)
                    <li class="flex items-start gap-3">
                        <div class="w-5 h-5 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5"
                             style="background: rgba(201,168,76,0.15)">
                            <svg class="w-3 h-3" style="color: var(--gold)" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd"
                                      d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </div>
                        <span class="text-gray-300 text-sm">{{ $benefit }}</span>
                    </li>
                    @endforeach
                </ul>

                <a href="/fidelizacion"
                   class="btn-gold px-8 py-3.5 rounded-sm text-xs uppercase tracking-[0.18em] font-bold inline-block">
                    Quiero ser VIP
                </a>
            </div>

            {{-- Card mockup --}}
            <div class="flex justify-center">
                <div class="relative w-72 md:w-80">
                    <div class="rounded-2xl p-7 relative overflow-hidden"
                         style="background: linear-gradient(135deg, #181818, #242424); border: 1px solid rgba(201,168,76,0.25)">

                        <div class="absolute -top-12 -right-12 w-40 h-40 rounded-full opacity-10 pointer-events-none"
                             style="background: radial-gradient(circle, var(--gold), transparent)"></div>

                        <div class="flex justify-between items-start mb-8">
                            <div>
                                <div class="text-xs tracking-widest text-gray-600 uppercase mb-1">Arte & Navaja</div>
                                <div class="font-display text-lg font-bold" style="color: var(--gold)">Club VIP</div>
                            </div>
                            <svg class="w-9 h-9 opacity-25" style="color: var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1"
                                      d="M6 18L18 6M8 6h10v10M6 12c0 3.314 2.686 6 6 6"/>
                            </svg>
                        </div>

                        <div class="mb-7">
                            <div class="text-xs text-gray-600 uppercase tracking-wider mb-1">Puntos acumulados</div>
                            <div class="font-display font-black leading-none" style="font-size: 4rem; color: var(--gold)">7</div>
                            <div class="text-xs text-gray-600 mt-1">de 10 para tu próximo premio</div>
                        </div>

                        <div class="mb-6">
                            <div class="flex justify-between text-xs text-gray-600 mb-2">
                                <span>Progreso</span><span>70%</span>
                            </div>
                            <div class="h-1.5 rounded-full" style="background: var(--border)">
                                <div class="h-full w-[70%] rounded-full"
                                     style="background: linear-gradient(90deg, var(--gold), var(--gold-light))"></div>
                            </div>
                        </div>

                        <div class="pt-5" style="border-top: 1px solid var(--border)">
                            <div class="text-xs text-gray-600 uppercase tracking-wider mb-1">Miembro desde</div>
                            <div class="text-sm text-white">Enero 2026</div>
                        </div>
                    </div>

                    <div class="absolute -bottom-5 -right-5 w-24 h-24 rounded-full opacity-15 pointer-events-none"
                         style="background: radial-gradient(circle, var(--gold), transparent)"></div>
                </div>
            </div>

        </div>
    </div>
</section>

<!-- ================================================
     FOOTER
================================================ -->
<footer style="background-color: #050505; border-top: 1px solid var(--border)">
    <div class="max-w-7xl mx-auto px-6 lg:px-10 py-12">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-10">

            <div>
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full overflow-hidden bg-white flex-shrink-0" style="padding:1px">
                        <img src="{{ asset('images/logo.jpeg') }}" alt="Arte & Navaja" class="w-full h-full object-contain">
                    </div>
                    <span class="font-display text-lg font-bold" style="color: var(--gold)">Arte & Navaja</span>
                </div>
                <p class="text-gray-700 text-sm leading-relaxed">
                    Barbería premium donde el estilo se convierte en arte. Tu imagen es nuestra pasión.
                </p>
            </div>

            <div>
                <h4 class="text-white text-xs font-semibold uppercase tracking-widest mb-5">Navegación</h4>
                <ul class="space-y-2.5">
                    <li><a href="#servicios"    class="text-gray-600 hover:text-white text-sm transition-colors">Servicios</a></li>
                    <li><a href="#galeria"      class="text-gray-600 hover:text-white text-sm transition-colors">Galería</a></li>
                    <li><a href="#testimonios"  class="text-gray-600 hover:text-white text-sm transition-colors">Opiniones</a></li>
                    <li><a href="/fidelizacion" class="text-sm transition-colors" style="color: var(--gold)">Club VIP →</a></li>
                </ul>
            </div>

            <div>
                <h4 class="text-white text-xs font-semibold uppercase tracking-widest mb-5">Contacto</h4>
                <ul class="space-y-3">
                    <li class="flex items-start gap-3 text-gray-600 text-sm">
                        <svg class="w-4 h-4 flex-shrink-0 mt-0.5" style="color: var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span>Tu dirección aquí</span>
                    </li>
                    <li class="flex items-center gap-3 text-gray-600 text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.948V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 7V5z"/>
                        </svg>
                        <span>Tu número aquí</span>
                    </li>
                    <li class="flex items-center gap-3 text-gray-600 text-sm">
                        <svg class="w-4 h-4 flex-shrink-0" style="color: var(--gold)" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span>Lun–Sáb: 9:00 – 20:00</span>
                    </li>
                </ul>
            </div>

        </div>

        <div class="mt-10 pt-8 text-center" style="border-top: 1px solid var(--border)">
            <p class="text-gray-800 text-xs">© {{ date('Y') }} Arte & Navaja. Todos los derechos reservados.</p>
        </div>
    </div>
</footer>

</body>
</html>
