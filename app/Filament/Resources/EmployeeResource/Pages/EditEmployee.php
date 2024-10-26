<?php

namespace App\Filament\Resources\EmployeeResource\Pages;

use App\Filament\Resources\EmployeeResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEmployee extends EditRecord
{
    protected static string $resource = EmployeeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            Actions\ForceDeleteAction::make(),
            Actions\RestoreAction::make(),
        ];
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $data['bonus_rate'] = $data['bonus_rate'] * 100;

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data['bonus_rate'] = $data['bonus_rate'] / 100;

        return $data;
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.kasir.resources.employees.index');
    }
}
