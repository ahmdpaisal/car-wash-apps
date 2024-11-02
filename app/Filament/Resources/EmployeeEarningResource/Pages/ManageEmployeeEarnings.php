<?php

namespace App\Filament\Resources\EmployeeEarningResource\Pages;

use App\Filament\Resources\EmployeeEarningResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;
use Filament\Resources\Components\Tab;
use Illuminate\Database\Eloquent\Builder;

class ManageEmployeeEarnings extends ManageRecords
{
    protected static string $resource = EmployeeEarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // Actions\CreateAction::make(),
        ];
    }

    // public function getTabs(): array
    // {
    //     $startOfMonth = now()->startOfMonth()->format('Y-m-d');
    //     $endOfMonth = now()->endOfMonth()->format('Y-m-d');
    //     return [
    //         'this-month' => Tab::make('Bulan Ini')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->whereBetween('earning_date', [$startOfMonth, $endOfMonth])),
    //         'this-year' => Tab::make('Tahun Ini')
    //             ->modifyQueryUsing(fn (Builder $query) => $query->where('earning_year', '>=', now()->format('Y'))),
    //         'all' => Tab::make('Semua'),
    //     ];
    // }
}
