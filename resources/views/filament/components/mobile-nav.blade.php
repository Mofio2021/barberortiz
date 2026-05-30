@php
use App\Models\User;
$user = auth()->user();
// Solo renderiza para barbero/cajero — admins usan el sidebar estándar
if (! $user instanceof User || ! $user->hasAnyRole(['barbero', 'cajero'])) return;

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
        'match' => 'admin/expenses*',
        'exact' => false,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z"/>',
    ],
    [
        'label' => 'Caja',
        'url'   => '/admin/cash-closings',
        'match' => 'admin/cash-closings*',
        'exact' => false,
        'svg'   => '<path stroke-linecap="round" stroke-linejoin="round" d="M15.75 15.75V18m-7.5-6.75h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V13.5Zm0 2.25h.008v.008H8.25v-.008Zm0 2.25h.008v.008H8.25V18Zm2.498-6.75h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V13.5Zm0 2.25h.007v.008h-.007v-.008Zm0 2.25h.007v.008h-.007V18Zm2.504-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5Zm0 2.25h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V18Zm2.498-6.75h.008v.008h-.008v-.008Zm0 2.25h.008v.008h-.008V13.5ZM8.25 6h7.5v2.25h-7.5V6ZM12 2.25c-1.892 0-3.758.11-5.593.322C5.307 2.7 4.5 3.684 4.5 4.832V18a2.25 2.25 0 0 0 2.25 2.25h10.5A2.25 2.25 0 0 0 19.5 18V4.832c0-1.15-.807-2.132-1.907-2.26A48.678 48.678 0 0 0 12 2.25Z"/>',
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

<nav
    class="fixed bottom-0 inset-x-0 z-50 lg:hidden
           bg-gray-900/95 backdrop-blur-sm border-t border-gray-700
           grid grid-cols-5"
    style="padding-bottom: env(safe-area-inset-bottom, 0px)"
>
    @foreach($nav as $item)
    @php
        $active = $item['exact']
            ? request()->is($item['match'])
            : request()->is($item['match']);
    @endphp
    <a href="{{ $item['url'] }}"
       class="flex flex-col items-center justify-center gap-0.5 py-2 min-h-[3.5rem]
              text-xs font-medium transition-colors touch-manipulation select-none
              {{ $active
                  ? 'text-amber-400 border-t-2 border-amber-400 -mt-px'
                  : 'text-gray-400 hover:text-gray-200' }}"
    >
        <svg class="w-6 h-6 shrink-0" fill="none" viewBox="0 0 24 24"
             stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            {!! $item['svg'] !!}
        </svg>
        <span class="leading-none">{{ $item['label'] }}</span>
    </a>
    @endforeach
</nav>
