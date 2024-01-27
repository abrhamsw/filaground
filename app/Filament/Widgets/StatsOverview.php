<?php

namespace App\Filament\Widgets;

use App\Enums\OrderStatusEnum;
use App\Models\Customer;
use App\Models\Order;
use App\Models\Product;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    
    protected static ?int $sort = 3;

    protected function getStats(): array
    {
        return [
            //
            Stat::make('Total Customer', Customer::count())->description("Increasing in Customer")->descriptionIcon('heroicon-m-arrow-trending-up')->color('success')
                ->chart([7,5,6,4,3,8]),

            Stat::make('Total Products', Product::count())->description("Total Product in app")->descriptionIcon('heroicon-m-arrow-trending-down')->color('danger')
            ->chart([7,5,6,4,3,8]),

            Stat::make('Pending Orders',Order::where('status',OrderStatusEnum::PENDING->value)->count())
                ->description("Total Product in app")->descriptionIcon('heroicon-m-arrow-trending-down')->color('danger')
                ->chart([7,5,6,4,3,8]),
        ];
    }
}
