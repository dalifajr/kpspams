<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GolonganTariff extends Model
{
    use HasFactory;

    protected $fillable = [
        'golongan_id',
        'meter_start',
        'meter_end',
        'price',
    ];

    public function golongan(): BelongsTo
    {
        return $this->belongsTo(Golongan::class);
    }
}
