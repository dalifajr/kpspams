<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Golongan extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
    ];

    public function tariffLevels(): HasMany
    {
        return $this->hasMany(GolonganTariff::class)->orderBy('meter_start');
    }

    public function nonAirFees(): HasMany
    {
        return $this->hasMany(GolonganNonAirFee::class)->orderBy('label');
    }

    public function customers(): HasMany
    {
        return $this->hasMany(Customer::class);
    }
}
