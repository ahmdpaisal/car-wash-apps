<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use Filament\Actions;
use Filament\Resources\Components\Tab;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;

class ListOrders extends ListRecords
{
    protected static string $resource = OrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'today' => Tab::make('Hari Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_date', now()->format('Y-m-d'))),
            'this-week' => Tab::make('Minggu Ini')
                ->modifyQueryUsing(fn (Builder $query) => $query->where('order_date', '>=', now()->subWeek()->format('Y-m-d'))),
            'all' => Tab::make('Semua'),
        ];
    }
}
