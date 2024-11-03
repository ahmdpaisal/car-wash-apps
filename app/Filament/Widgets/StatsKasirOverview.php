<?php

namespace App\Filament\Widgets;

use App\Models\Employee;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsKasirOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Pegawai', Employee::query()->count())
                ->icon('heroicon-o-users')
                ->description('Semua Pegawai')
                ->color('success'),
            Stat::make('Pendapatan', 'Rp ' . OrderPayment::query()->where('payment_date', '>=',now()->format('Y-m-d'))->sum('bill_amount'))
                ->icon('heroicon-o-banknotes')
                ->description('Jumlah Pendapatan Hari Ini')
                ->color('info'),
            Stat::make(
                'Order',
                OrderDetail::query()
                    ->join('orders', 'orders.id', '=', 'order_details.order_id')
                    // ->whereNull('orders.deleted_at')
                    // ->where('orders.order_status', '!=', 'Dibatalkan')
                    ->where('orders.order_date', now()->format('Y-m-d'))
                    ->count()
            )
                ->icon('heroicon-o-shopping-cart')
                ->description('Jumlah Order Hari Ini')
                ->color('warning'),
        ];
    }
}
