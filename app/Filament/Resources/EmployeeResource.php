<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Employee;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Infolists\Components\Grid;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\DeleteBulkAction;
use Filament\Tables\Actions\EditAction;
use Filament\Tables\Actions\ForceDeleteAction;
use Filament\Tables\Actions\ForceDeleteBulkAction;
use Filament\Tables\Actions\RestoreAction;
use Filament\Tables\Actions\RestoreBulkAction;
use Filament\Tables\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $label = 'Karyawan';

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Admin';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('first_name')
                    ->label('Nama Depan')
                    ->required()
                    ->maxLength(255),
                TextInput::make('last_name')
                    ->label('Nama Belakang')
                    ->maxLength(255),
                Select::make('gender')
                    ->label('Kelamin')
                    ->options([
                        'Male' => 'Laki-Laki',
                        'Female' => 'Perempuan'
                    ])
                    ->native(false)
                    ->required(),
                DatePicker::make('birth_date')
                    ->label('Tgl Lahir')
                    ->required(),
                Textarea::make('address')
                    ->label('Alamat')
                    ->autosize()
                    ->columnSpanFull(),
                TextInput::make('phone_number')
                    ->label('No. HP')
                    ->unique('employees', 'phone_number', ignoreRecord: true)
                    ->tel()
                    ->maxLength(255),
                TextInput::make('bonus_rate')
                    ->label('Bonus')
                    ->numeric()
                    ->default(5)
                    ->minValue(1)
                    ->maxValue(100)
                    ->suffix('%'),
                Select::make('position')
                    ->label('Posisi')
                    ->relationship('position', 'name')
                    ->required(),
                Select::make('user_id')
                    ->relationship(
                        name: 'user',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn (Builder $query) => $query->doesntHave('employee')
                    )
                    ->searchable()
                    ->preload()
                    ->optionsLimit(5)
                    ->hiddenOn(['edit', 'view']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('first_name')
                    ->label('Nama Depan')
                    ->searchable(),
                TextColumn::make('last_name')
                    ->label('Nama Belakang')
                    ->searchable(),
                TextColumn::make('gender')
                    ->label('Kelamin')
                    ->formatStateUsing(fn (string $state) => $state == 'Female' ? 'Perempuan' : 'Laki - Laki'),
                TextColumn::make('birth_date')
                    ->label('Tgl Lahir')
                    ->date('d F Y'),
                TextColumn::make('phone_number')
                    ->label('No. HP'),
                // TextColumn::make('is_active'),
                TextColumn::make('created_at')
                    ->label('Dibuat Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('Diupadate Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->label('Dihapus Pada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
                ForceDeleteAction::make(),
                RestoreAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function infoList(Infolist $infolist): Infolist
    {
        return $infolist
            ->columns(3)
            ->schema([
                TextEntry::make('first_name')->label('Nama Depan'),
                TextEntry::make('last_name')->label('Nama Belakang'),
                TextEntry::make('gender')
                    ->label('Jenis Kelamin')
                    ->state(function (Employee $employee) {
                        return $employee->gender == 'Female' ? 'Perempuan' : 'Laki - Laki';
                    }),
                TextEntry::make('birth_date')
                    ->label('Tgl Lahir')
                    ->date(),
                TextEntry::make('address')->label('Alamat'),
                TextEntry::make('phone_number')->label('No. HP'),
                TextEntry::make('position.name')->label('Posisi'),
                TextEntry::make('bonus_rate')
                    ->label('Bonus')
                    ->state(function (Employee $employee) {
                        if ($employee->position->name == 'Karyawan') return $employee->bonus_rate * 100 .'%';
                        
                        return 'Tidak Ditampilkan';
                    }),
                
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
