<?php

namespace App\Filament\Resources\OrderResource\Pages;

use App\Filament\Resources\OrderResource;
use App\Models\Order;
use Carbon\Carbon;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\CreateRecord;

class CreateOrder extends CreateRecord
{
    protected static string $resource = OrderResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $orderDate = Carbon::parse($data['order_date']);
        $orderCode = Order::generateOrderCode($data['order_date']);
        $year = $orderDate->format('Y');
        
        $data['order_code'] = $orderCode;
        $data['order_year'] = $year;
        $data['order_status'] = 'Dalam Antrian';
        $data['payment_status'] = 'Belum Lunas';

        return $data;   
    }

    protected function getRedirectUrl(): string
    {
        return route('filament.kasir.resources.orders.index');
    }
}
