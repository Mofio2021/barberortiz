@php
use App\Models\User;
$navUser = auth()->user();
$showMobileNav = $navUser instanceof User && $navUser->hasAnyRole(['barbero', 'cajero']);

$nav = [
    [
        'label' => 'Inicio',
        'url'   => '/admin',
        'match' => 'admin',
        'exact' => true,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25"/>',
    ],
    [
        'label' => 'POS',
        'url'   => '/admin/pos',
        'match' => 'admin/pos',
        'exact' => true,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 0 0-3 3h15.75m-12.75-3h11.218c1.121-2.3 2.1-4.684 2.924-7.138a60.114 60.114 0 0 0-16.536-1.84M7.5 14.25 5.106 5.272M6 20.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Zm12.75 0a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0Z"/>',
    ],
    [
        'label' => 'Egresos',
        'url'   => '/admin/expenses',
        'match' => 'admin/expenses',
        'exact' => false,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
    ],
    [
        'label' => 'Ventas',
        'url'   => '/admin/sales',
        'match' => 'admin/sales',
        'exact' => false,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
    ],
    [
        'label' => 'Perfil',
        'url'   => '/admin/profile',
        'match' => 'admin/profile',
        'exact' => true,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>',
    ],
];
@endphp

@if($showMobileNav)
{{-- ═══════════════════════════════════════════════════════════════
     MOBILE BOTTOM NAV
     El layout (flex horizontal) se define con style="" para garantizar
     que funcione SIN depender del build de Tailwind/Vite.
     Las clases de color/borde usan CSS de Filament como fallback visual.
     ═══════════════════════════════════════════════════════════════ --}}
<nav
    class="barber-mobile-nav"
    style="
        position: fixed;
        bottom: 0;
        left: 0;
        right: 0;
        z-index: 9999;
        display: flex;
        flex-direction: row;
        align-items: stretch;
        background: rgba(17,24,39,0.97);
        border-top: 1px solid #374151;
        padding-bottom: env(safe-area-inset-bottom, 0px);
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
    "
    aria-label="Navegación móvil"
>
    @foreach($nav as $item)
    @php
        $active = $item['exact']
            ? request()->is($item['match'])
            : request()->is($item['match'] . '*');
    @endphp
    <a href="{{ $item['url'] }}"
       style="
           flex: 1;
           display: flex;
           flex-direction: column;
           align-items: center;
           justify-content: center;
           gap: 2px;
           padding: 8px 2px;
           min-height: 56px;
           text-decoration: none;
           font-size: 10px;
           font-weight: 600;
           transition: color 0.15s;
           color: {{ $active ? '#fbbf24' : '#9ca3af' }};
           border-top: 2px solid {{ $active ? '#fbbf24' : 'transparent' }};
           margin-top: -2px;
           -webkit-tap-highlight-color: transparent;
           user-select: none;
       "
    >
        <svg style="width:22px;height:22px;flex-shrink:0;"
             fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            {!! $item['svg'] !!}
        </svg>
        <span style="line-height:1;white-space:nowrap;">{{ $item['label'] }}</span>
    </a>
    @endforeach
</nav>
@endif
