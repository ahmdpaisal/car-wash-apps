<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Employee extends Model
{
    use SoftDeletes;

    protected $guarded = ['id'];

    public function user(): BelongsTo {
        return $this->belongsTo(User::class);
    }
    
    public function position(): BelongsTo {
        return $this->belongsTo(Position::class);
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(EmployeeEarning::class);
    }
}
