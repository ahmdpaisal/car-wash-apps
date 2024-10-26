<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateEmployee extends CreateRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['bonus_rate'] = $data['bonus_rate'] / 100;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.kasir.resources.employees.index');
    }
}
