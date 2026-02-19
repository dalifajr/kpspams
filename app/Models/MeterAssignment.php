<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeterAssignment extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_period_id',
        'area_id',
        'target_customers',
        'completed_customers',
        'total_volume',
        'total_bill',
        'status',
    ];

    protected $casts = [
        'total_volume' => 'decimal:3',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(MeterPeriod::class, 'meter_period_id');
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function readings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }
}
