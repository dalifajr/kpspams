<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bill extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_PUBLISHED = 'published';
    public const STATUS_PARTIAL = 'partial';
    public const STATUS_PAID = 'paid';

    protected $fillable = [
        'meter_reading_id',
        'customer_id',
        'meter_period_id',
        'year',
        'month',
        'water_usage_amount',
        'admin_fee',
        'other_fees',
        'total_amount',
        'paid_amount',
        'status',
        'published_by',
        'published_at',
        'paid_at',
        'notes',
    ];

    protected $casts = [
        'water_usage_amount' => 'integer',
        'admin_fee' => 'integer',
        'other_fees' => 'integer',
        'total_amount' => 'integer',
        'paid_amount' => 'integer',
        'published_at' => 'datetime',
        'paid_at' => 'datetime',
    ];

    protected $appends = ['remaining'];

    public function meterReading(): BelongsTo
    {
        return $this->belongsTo(MeterReading::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function period(): BelongsTo
    {
        return $this->belongsTo(MeterPeriod::class, 'meter_period_id');
    }

    public function publishedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function remainingAmount(): int
    {
        return max($this->total_amount - $this->paid_amount, 0);
    }

    public function getRemainingAttribute(): int
    {
        return $this->remainingAmount();
    }

    public function isFullyPaid(): bool
    {
        return $this->paid_amount >= $this->total_amount;
    }

    public function isPublished(): bool
    {
        return $this->status !== self::STATUS_DRAFT;
    }

    public function updatePaymentStatus(): void
    {
        $this->paid_amount = (int) $this->payments()->sum('amount');

        if ($this->paid_amount >= $this->total_amount) {
            $this->status = self::STATUS_PAID;
            $this->paid_at = now();
        } elseif ($this->paid_amount > 0) {
            $this->status = self::STATUS_PARTIAL;
            $this->paid_at = null;
        } else {
            $this->status = self::STATUS_PUBLISHED;
            $this->paid_at = null;
        }

        $this->save();
    }
}
