<?php

namespace App\Filament\Widgets;

use App\Models\Product;
use App\Models\User;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Auth;

class CriticalStockWidget extends Widget
{
    protected static string $view = 'filament.widgets.critical-stock-widget';
    protected static ?int   $sort = 5;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        $user = Auth::user();
        return $user instanceof User
            && $user->hasAnyRole(['super_admin', 'admin_sucursal']);
    }

    protected function getViewData(): array
    {
        $user = Auth::user();

        $query = Product::with(['branch', 'category'])
            ->where('is_active', true)
            ->whereColumn('stock', '<=', 'stock_min')
            ->orderByRaw('CASE WHEN stock <= 0 THEN 0 ELSE 1 END') // agotados primero
            ->orderBy('stock');

        // admin_sucursal solo ve los productos de su sucursal (globales + propios)
        if ($user instanceof User && $user->hasRole('admin_sucursal')) {
            $query->forBranch($user->branch_id);
        }

        $products = $query->get();

        $agotados  = $products->filter(fn ($p) => $p->stock <= 0)->count();
        $stockBajo = $products->filter(fn ($p) => $p->stock > 0)->count();

        return compact('products', 'agotados', 'stockBajo');
    }
}
