<?php

namespace App\Filament\Resources\OrderPaymentResource\Pages;

use App\Filament\Resources\OrderPaymentResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrderPayments extends ListRecords
{
    protected static string $resource = OrderPaymentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_date', '>=', today())),
            'this-week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('payment_date', '>=', now()->subWeek())),
            'all' => Tab::make('Semua'),
        ];
    }
}
