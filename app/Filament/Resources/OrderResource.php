<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Employee;
use App\Models\EmployeeEarning;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\OrderPayment;
use App\Models\Product;
use Carbon\Carbon;
use Filament\Forms;
use Filament\Forms\Components\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Repeater;
use Filament\Forms\components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Infolists\Components\Actions\Action as ActionInfoList;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\Tabs;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Resources\Resource;
use Filament\Support\Enums\FontWeight;
use Filament\Tables;
use Filament\Tables\Actions\Action as ActionTables;
use Filament\Tables\Actions\ActionGroup;
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
use Filament\Support\RawJs;
use Illuminate\Support\Facades\Auth;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-cart';

    protected static ?string $navigationGroup = 'Kasir';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make()
                    ->columns([
                        'sm' => 4,
                    ])
                    ->schema([
                        TextInput::make('customer_name')
                            ->label('Nama Pelanggan'),
                        TextInput::make('kasir')
                            ->label('Kasir')
                            ->disabled()
                            ->hiddenOn('edit')
                            ->default(Auth::user()->name),
                        Hidden::make('user_id')
                            ->disabledOn('edit')
                            ->default(Auth::user()->id),
                        TextInput::make('user')
                            ->label('Kasir')
                            ->disabled()
                            ->hiddenOn('create'),

                        DatePicker::make('order_date')
                            ->label('Tgl Order')
                            ->native(false)
                            ->default(now())
                            ->closeOnDateSelection()
                            ->required(),
                        TimePicker::make('order_time')
                            ->label('Waktu Order')
                            ->native(false)
                            ->default(now())
                            ->closeOnDateSelection()
                            ->required(),
                        
                    ]),
                Section::make()
                    ->schema([
                        static::getOrderDetailRepeater(),
                        TextInput::make('total')
                            ->numeric()
                            ->prefix('Rp')
                            ->readOnly(),
                    ])

                
            ]);
    }

    public static function getEmployeeFormField(): Select
    {
        return Select::make('employee_id')
            ->label('Petugas')
            ->relationship(
                'employee',
                'first_name',
                fn (Builder $query) => $query->whereNull('user_id')
            )
            ->getOptionLabelFromRecordUsing(fn (Employee $employee) => "{$employee->first_name} {$employee->last_name}")
            ->searchable('employee.first_name')
            ->preload()
            ->optionsLimit(5)
            ->required();
    }

    public static function getOrderDetailRepeater(): Repeater
    {
        return Repeater::make('orderDetails')
            ->label('Order Detail')
            ->columns(4)
            ->columnSpanFull()
            ->relationship()
            ->schema([
                Select::make('product_id')
                    ->label('Produk')
                    ->relationship('product', 'name')
                    ->searchable('product.name')
                    ->preload()
                    ->optionsLimit(5)
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set) {
                        $product = Product::find($state);

                        $set('price', $product->price);
                        $set('product_name', $product->name);
                    })
                    ->required(),
                TextInput::make('price')
                    ->label('Harga')
                    ->numeric()
                    ->readonly()
                    ->required(),
                TextInput::make('quantity')
                    ->label('Jumlah')
                    ->numeric()
                    ->default(1)
                    ->required(),
                static::getEmployeeFormField(),
                
            ])
            ->addActionLabel('Tambah Order Lain')
            ->collapsible()
            ->required()
            ->minItems(1)
            ->live()
            ->afterStateUpdated(function (Get $get, Set $set) {
                self::updateTotals($get, $set);
            })
            ->deleteAction(
                fn(Action $action) => $action->after(fn(Get $get, Set $set) => self::updateTotals($get, $set))
            )
            ->mutateRelationshipDataBeforeCreateUsing(function (array $data): array {
                $product = Product::find($data['product_id']);

                $data['product_name'] = $product->name;
                $data['price'] = $product->price;
                $data['total_price'] = $product->price * $data['quantity'];

                return $data;
            })
            ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                $product = Product::find($data['product_id']);

                $data['product_name'] = $product->name;
                $data['price'] = $product->price;
                $data['total_price'] = $product->price * $data['quantity'];

                return $data;
            });
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(false)
            ->columns([
                TextColumn::make('order_code')
                    ->label('No. Order')
                    ->searchable(),
                TextColumn::make('order_date')
                    ->label('Tgl Order'),
                TextColumn::make('order_status')
                    ->label('Status Order')
                    ->badge()
                    ->color(fn (string $state): string => match ($state){
                        'Dalam Antrian' => 'gray',
                        'Diproses' => 'warning',
                        'Selesai' => 'success',
                        'Dibatalkan' => 'danger',
                    }),
                TextColumn::make('payment_status')
                    ->label('Status Pemb.')
                    ->badge()
                    ->color(fn (string $state): string => match ($state){
                        'Belum Lunas' => 'danger',
                        'Lunas' => 'success',
                    }),
                TextColumn::make('total_price')
                    ->label('Total Tagihan')
                    ->money('IDR'),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->orderBy('order_date', 'desc'))
            ->filters([
                TrashedFilter::make(),
            ])
            ->actions([
                ActionTables::make('paymentProcess')
                    ->label('Proses Pembayaran')
                    ->icon('heroicon-o-banknotes')
                    ->iconButton()
                    ->color('warning')
                    ->visible(function (Order $order) {
                        return $order->payment_status != 'Lunas' || $order->order_status == 'Dibatalkan';
                    })
                    ->fillForm(fn (Order $order): array => [
                        'order_id' => $order->id,
                        'bill_amount' => $order->orderDetails->sum('total_price'),
                        'bill_amount_display' => $order->orderDetails->sum('total_price'),
                        'payment_method' => 'Cash',
                        'payment_date' => now(),
                    ])
                    ->form([
                        Hidden::make('order_id'),
                        Select::make('payment_method')
                            ->label('Metode Pembayaran')
                            ->native(false)
                            ->options([
                                'Cash' => 'Cash',
                                'Transfer' => 'Transfer',
                            ])
                            ->required(),
                        DateTimePicker::make('payment_date')
                            ->label('Waktu Pembayaran')
                            ->required(),
                        TextInput::make('bill_amount_display')
                            ->label('Total Tagihan')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->prefix('Rp')
                            ->disabled(),
                        Hidden::make('bill_amount')
                            ->label('Total Tagihan'),
                        TextInput::make('payment_amount')
                            ->label('Total Bayar')
                            ->mask(RawJs::make('$money($input)'))
                            ->stripCharacters(',')
                            ->numeric()
                            ->gte('bill_amount')
                            ->prefix('Rp')
                            ->required(),
                    ])
                    ->requiresConfirmation()
                    ->modalIcon('heroicon-o-receipt-percent')
                    ->modalDescription('Pastikan Jumlah Tagihan Sudah Benar')
                    ->action(function (array $data, Order $order): void {

                        $payment_date = $data['payment_date'];
                        $payment_year = Carbon::parse($data['payment_date'])->format('Y');
                        
                        //Create order payment
                        $orderPayment = OrderPayment::create([
                            'bill_amount' => $data['bill_amount'],
                            'payment_amount' => $data['payment_amount'],
                            'change' => $data['payment_amount'] - $data['bill_amount'],
                            'payment_method' => $data['payment_method'],
                            'payment_date' => $payment_date,
                            'payment_year' => $payment_year,
                            'order_id' => $data['order_id'],
                            'user_id' => Auth::user()->id,
                        ]);

                        //Create employee's earnings
                        $orderDetails = OrderDetail::where('order_id', $order->id)->get();
                        
                        foreach($orderDetails as $orderDetail) {
                            $earnings = $orderDetail->calculateEarnings();

                            EmployeeEarning::create([
                                'employee_id' => $orderDetail->employee_id,
                                'order_detail_id' => $orderDetail->id,
                                'order_payment_id' => $orderPayment->id,
                                'salary_amount' => $earnings['salary'],
                                'bonus_amount' => $earnings['bonus'],
                                'total_earning' => $earnings['total'],
                                'owner_share' => $earnings['owner_share'],
                                'earning_date' => $payment_date,
                                'earning_year' => $payment_year,
                            ]);
                        }
                        
                        //Update order's payment status
                        $order->payment_status = 'Lunas';
                        $order->save();
                    })
                    ->after(function ($action, Order $order){
                        $action->redirect(route('order_payment_invoice', base64_encode($order->order_code)));

                    }),
                ActionTables::make('changeOrderStatus')
                    ->label('Ubah Status Order')
                    ->icon('heroicon-o-arrow-path')
                    ->iconButton()
                    ->color('info')
                    ->visible(function (Order $order) {
                        return $order->payment_status != 'Lunas';
                    })
                    ->fillForm(fn (Order $order): array => [
                        'order_status' => $order->order_status,
                    ])
                    ->form([
                        Select::make('order_status')
                            ->label('Status Order')
                            ->native(false)
                            ->options([
                                'Dalam Antrian' => 'Dalam Antrian',
                                'Diproses' => 'Diproses',
                                'Selesai' => 'Selesai',
                                'Dibatalkan' => 'Dibatalkan',
                            ])
                            ->required(),
                    ])
                    ->action(function (array $data, Order $order): void {
                        $order->order_status = $data['order_status'];
                        $order->save();
                    }),
                ActionGroup::make([
                    ViewAction::make(),
                    EditAction::make()
                        ->visible(function (Order $order) {
                            return $order->payment_status != 'Lunas';
                        }),
                    DeleteAction::make()
                        ->visible(function (Order $order) {
                            return $order->payment_status != 'Lunas';
                        }),
                ]),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->checkIfRecordIsSelectableUsing(
                fn (Order $order): bool => in_array($order->order_status, ['Dibatalkan']) && $order->payment_status != 'Lunas'
            );
    }

    public static function infoList(Infolist $infolist): Infolist
    {
        return $infolist
            ->schema([
                Tabs::make('Tabs')
                    ->columnSpanFull()
                    ->tabs([
                        Tabs\Tab::make('Order')
                            ->columns([
                                'sm' => 3
                            ])
                            ->schema([
                                TextEntry::make('order_code')
                                    ->label('No. Order')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('order_date')
                                    ->label('Tgl Order')
                                    ->weight(FontWeight::Bold),
                                TextEntry::make('order_status')
                                    ->label('Status Order')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state){
                                        'Dalam Antrian' => 'gray',
                                        'Diproses' => 'warning',
                                        'Selesai' => 'success',
                                        'Dibatalkan' => 'danger',
                                    }),
                                TextEntry::make('payment_status')
                                    ->label('Status Pembayaran')
                                    ->badge()
                                    ->color(fn (string $state): string => match ($state){
                                        'Belum Lunas' => 'danger',
                                        'Lunas' => 'success',
                                    }),
                                TextEntry::make('total_price')
                                    ->label('Total Tagihan')
                                    ->money('IDR')
                                    ->weight(FontWeight::Bold),
                            ]),
                        Tabs\Tab::make('Order Detail')
                            ->schema([
                                RepeatableEntry::make('orderDetails')
                                    ->label('')
                                    ->schema([
                                        TextEntry::make('product_name')
                                            ->label('Nama'),
                                        TextEntry::make('price')
                                            ->label('Harga'),
                                        TextEntry::make('quantity')
                                            ->label('Jumlah'),
                                        TextEntry::make('total_price')
                                            ->money('IDR')
                                            ->label('Total Harga'),
                                        TextEntry::make('employee.first_name')
                                            ->label('Petugas')
                                            ->columnSpan(2),
                                    ])
                                    ->grid(2)
                                    ->columns(2)
                            ]),
                        Tabs\Tab::make('Pembayaran')
                            ->columns([
                                'sm' => 3
                            ])
                            ->schema([
                                TextEntry::make('orderPayment.bill_amount')
                                    ->label('Total Tagihan')
                                    ->money('IDR'),
                                TextEntry::make('orderPayment.payment_amount')
                                    ->label('Total Pembayaran')
                                    ->money('IDR'),
                                TextEntry::make('orderPayment.change')
                                    ->label('Kembalian')
                                    ->money('IDR'),
                                TextEntry::make('orderPayment.payment_method')
                                    ->label('Metode'),
                                TextEntry::make('orderPayment.payment_date')
                                    ->label('Waktu Pembayaran')
                                    ->dateTime('d F Y H:i:s'),
                                TextEntry::make('orderPayment.user.name')
                                    ->label('User'),
                            ])
                                        
                    ])
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    // This function updates totals based on the selected products and quantities
    public static function updateTotals(Get $get, Set $set): void
    {
        // Retrieve all selected products and remove empty rows
        $selectedProducts = collect($get('orderDetails'))->filter(fn($item) => !empty($item['product_id']) && !empty($item['quantity']));
    
        // Retrieve prices for all selected products
        $prices = Product::find($selectedProducts->pluck('product_id'))->pluck('price', 'id');
    
        // Calculate subtotal based on the selected products and quantities
        $subtotal = $selectedProducts->reduce(function ($subtotal, $product) use ($prices) {
            return $subtotal + ($prices[$product['product_id']] * $product['quantity']);
        }, 0);
    
        // Update the state with the new values
        $set('total', number_format($subtotal, 0, '.', ''));
    }
}
