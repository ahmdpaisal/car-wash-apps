<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Order extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public static function generateOrderCode($orderDate)
    {
        // Format tanggal input user (DDMMYYYY)
        $orderMonth = Carbon::parse($orderDate)->format('dmY');

        // Cari nomor order terakhir di bulan dan tahun tersebut
        $lastOrder = Order::where('order_code', 'LIKE', $orderMonth . '%')
                        ->orderBy('order_code', 'desc')
                        ->withTrashed()
                        ->first();

        // Jika belum ada order di bulan tersebut, nomor urut mulai dari 1
        $lastSequence = $lastOrder ? (int)substr($lastOrder->order_code, -4) : 0;

        // Tambahkan 1 untuk nomor order berikutnya
        $nextSequence = str_pad($lastSequence + 1, 4, '0', STR_PAD_LEFT); // hasilnya 0001, 0002, dst.

        // Format nomor order: DDMMYYYY-0001
        return $orderMonth . '-' . $nextSequence;
    }

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }

    public function employee(): BelongsTo {
        return $this->belongsTo(Employee::class);
    }

    public function getBonusAttribute() {
        $totalPrice = $this->orderDetails->sum('total_price');
        $bonusPercentage = 0.05; //5%
        
        return $totalPrice * $bonusPercentage;
    }

    public function orderDetails(): HasMany {
        return $this->hasMany(OrderDetail::class);
    }

    public function getTotalPriceAttribute() {
        return $this->orderDetails->sum('total_price');
    }

    public function orderPayment(): HasOne {
        return $this->hasOne(OrderPayment::class);
    }
}
