<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderDetail extends Model
{
    protected $guarded = ['id'];

    public function employee(): BelongsTo {
        return $this->belongsTo(Employee::class);
    }

    public function order(): BelongsTo {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo {
        return $this->belongsTo(Product::class);
    }

    public function calculateEarnings(): array
    {
        $employeeBonusRate = $this->employee->bonus_rate;
        $totalShare = $this->total_price * 0.5;
        $employeeShare = $totalShare;
        $ownerShare = $totalShare;

        $bonus = $ownerShare * $employeeBonusRate;
        $totalEarnings = $employeeShare + $bonus;

        return [
            'owner_share' => $ownerShare,
            'salary' => $employeeShare,
            'bonus' => $bonus,
            'total' => $totalEarnings,
        ];
    }
}
