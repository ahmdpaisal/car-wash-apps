<?php

use App\Http\Controllers\OrderPaymentController;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

Route::get('/order-payments/invoice/{record}', [OrderPaymentController::class, 'invoicePDF'])
  ->middleware(['auth'])
  ->name('order_payment_invoice');
