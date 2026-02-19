<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_code',
        'name',
        'address_short',
        'phone_number',
        'area_id',
        'golongan_id',
        'family_members',
        'meter_reading',
    ];

    public function area(): BelongsTo
    {
        return $this->belongsTo(Area::class);
    }

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(Golongan::class);
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function meterReadings(): HasMany
    {
        return $this->hasMany(MeterReading::class);
    }

    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function scopeForUser(Builder $query, User $user): Builder
    {
        if ($user->isAdmin()) {
            return $query;
        }

        if ($user->isPetugas()) {
            $areaIds = $user->assignedAreas()->pluck('areas.id');
            if ($areaIds->isNotEmpty()) {
                return $query->whereIn('area_id', $areaIds);
            }

            if ($user->area_id) {
                return $query->where('area_id', $user->area_id);
            }
        }

        return $query->whereRaw('1 = 0');
    }
}
