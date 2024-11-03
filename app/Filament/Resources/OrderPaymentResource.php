<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderPaymentResource\Pages;
use App\Filament\Resources\OrderPaymentResource\RelationManagers;
use App\Models\EmployeeEarning;
use App\Models\Order;
use App\Models\OrderPayment;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderPaymentResource extends Resource
{
    protected static ?string $model = OrderPayment::class;

    protected static ?string $label = 'Pembayaran';

    protected static ?string $navigationIcon = 'heroicon-o-wallet';

    protected static ?string $navigationGroup = 'Kasir';

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
                TextColumn::make('order.order_code')
                    ->label('No. Order')
                    ->searchable(),
                TextColumn::make('bill_amount')
                    ->label('Total Tagihan')
                    ->money('IDR'),
                TextColumn::make('payment_amount')
                    ->label('Total Pembayaran')
                    ->money('IDR'),
                TextColumn::make('change')
                    ->label('Kembalian')
                    ->money('IDR'),
                TextColumn::make('payment_date')
                    ->label('Waktu Pembayaran')
                    ->dateTime('d-m-Y H:i:s')
                    ->sortable(),
                TextColumn::make('user.name')
                    ->label('User'),
            ])
            ->filters([
                // Tables\Filters\TrashedFilter::make(),
                
            ])
            ->actions([
                // Tables\Actions\EditAction::make(),
                Action::make('Cetak Invoice')
                    ->icon('heroicon-o-printer')
                    ->iconButton()
                    ->url(fn ($record) => route('order_payment_invoice', base64_encode($record->order->order_code))),
                Action::make('Batal Bayar')
                    ->icon('heroicon-o-x-circle')
                    ->iconButton()
                    ->color('danger')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        
                        $order = Order::find($record->order_id);
                        $order->payment_status = 'Belum Lunas';
                        $order->save();

                        EmployeeEarning::where('order_payment_id', $record->id)->delete();

                        $record->delete();
                    }),
            ])
            // ->bulkActions([
            //     Tables\Actions\BulkActionGroup::make([
            //         Tables\Actions\DeleteBulkAction::make(),
            //         Tables\Actions\ForceDeleteBulkAction::make(),
            //         Tables\Actions\RestoreBulkAction::make(),
            //     ]),
            // ])
            ;
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
            'index' => Pages\ListOrderPayments::route('/'),
            // 'create' => Pages\CreateOrderPayment::route('/create'),
            // 'edit' => Pages\EditOrderPayment::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                // SoftDeletingScope::class,
            ]);
    }
}
