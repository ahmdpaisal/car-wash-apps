<?php

namespace App\Filament\Resources\EmployeeEarningResource\Pages;

use App\Filament\Resources\EmployeeEarningResource;
use Filament\Actions;
use Filament\Resources\Pages\ManageRecords;

class ManageEmployeeEarnings extends ManageRecords
{
    protected static string $resource = EmployeeEarningResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
