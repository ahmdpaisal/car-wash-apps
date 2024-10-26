<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeEarningResource\Pages;
use App\Filament\Resources\EmployeeEarningResource\RelationManagers;
use App\Models\Employee;
use App\Models\EmployeeEarning;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Grouping\Group;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeEarningResource extends Resource
{
    protected static ?string $model = EmployeeEarning::class;

    protected static ?string $label = 'Penggajian';

    protected static ?string $navigationIcon = 'heroicon-o-credit-card';

    protected static ?string $navigationGroup = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('salary_amount')->label('Gaji')->money('IDR'),
                TextColumn::make('bonus_amount')->label('Bonus')->money('IDR'),
                TextColumn::make('total_earning')->label('Total')->money('IDR'),
                TextColumn::make('earning_date')->label('Tanggal')->date('d F Y'),
                TextColumn::make('status')->label('Status')
                    ->badge()
                    ->color(fn (string $state): string => match ($state){
                        'Belum Diambil' => 'warning',
                        'Sudah Diambil' => 'success',
                    }),
            ])
            ->groups([
                Group::make('employee_id')
                    ->label('Nama Karyawan')
                    ->collapsible()
                    ->titlePrefixedWithLabel(false)
                    ->getTitleFromRecordUsing(fn (EmployeeEarning $record): string => $record->employee->first_name.' '.$record->employee->last_name)
            ])
            ->groupingSettingsInDropdownOnDesktop()
            ->defaultGroup('employee_id')
            ->filters([
                SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'Belum Diambil' => 'Belum Diambil',
                        'Sudah Diambil' => 'Sudah Diambil',
                    ]
                    ),
            ])
            ->actions([
                
            ])
            ->bulkActions([
                BulkAction::make('changeStatus')
                    ->label('Ubah Status')
                    ->requiresConfirmation()
                    ->action(function (Collection $records){
                        $records->each->update(['status' => 'Sudah Diambil']);
                    }),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (EmployeeEarning $record): bool => $record->status && $record->status === 'Belum Diambil',
            );;
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ManageEmployeeEarnings::route('/'),
        ];
    }
}
