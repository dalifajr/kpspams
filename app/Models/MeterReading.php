<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class MeterReading extends Model
{
    use HasFactory;

    protected $fillable = [
        'meter_period_id',
        'meter_assignment_id',
        'customer_id',
        'area_id',
        'petugas_id',
        'start_reading',
        'end_reading',
        'usage_m3',
        'bill_amount',
        'status',
        'is_estimated',
        'note',
        'photo_path',
        'recorded_at',
        'bill_published_at',
        'bill_published_by',
    ];

    protected $casts = [
        'start_reading' => 'decimal:3',
        'end_reading' => 'decimal:3',
        'usage_m3' => 'decimal:3',
        'bill_amount' => 'integer',
        'recorded_at' => 'datetime',
        'is_estimated' => 'boolean',
        'bill_published_at' => 'datetime',
    ];

    public function period(): BelongsTo
    {
        return $this->belongsTo(MeterPeriod::class, 'meter_period_id');
    }

    public function assignment(): BelongsTo
    {
        return $this->belongsTo(MeterAssignment::class, 'meter_assignment_id');
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function petugas(): BelongsTo
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }

    public function billPublishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'bill_published_by');
    }

    public function bill(): HasOne
    {
        return $this->hasOne(Bill::class);
    }
}
