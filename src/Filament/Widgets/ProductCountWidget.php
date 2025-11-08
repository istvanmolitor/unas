<?php

namespace Molitor\Unas\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Molitor\Product\Models\Product;
use Molitor\Unas\Models\UnasProduct;

class ProductCountWidget extends BaseWidget
{
    protected function getStats(): array
    {
        $productCount = Product::count();
        $unasProductCount = UnasProduct::count();
        $activeProductCount = Product::where('active', true)->count();

        return [
            Stat::make('Összes termék', $productCount)
                ->description('Termékek száma a rendszerben')
                ->descriptionIcon('heroicon-m-cube')
                ->color('success'),

            Stat::make('Aktív termékek', $activeProductCount)
                ->description('Aktív termékek száma')
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('primary'),

            Stat::make('UNAS termékek', $unasProductCount)
                ->description('UNAS-ból szinkronizált termékek')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color('warning'),
        ];
    }
}
