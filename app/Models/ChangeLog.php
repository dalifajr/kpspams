<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;

class ChangeLog extends Model
{
    protected $fillable = [
        'user_id',
        'role',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'before',
        'after',
        'undo',
        'undone_at',
    ];

    protected $casts = [
        'before' => 'array',
        'after' => 'array',
        'undo' => 'array',
        'undone_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public static function record(?User $user, string $action, string $description, array $payload = []): self
    {
        if (!Schema::hasTable('change_logs')) {
            return new self();
        }

        try {
            return self::create([
                'user_id' => $user?->id,
                'role' => $user?->role,
                'action' => $action,
                'subject_type' => $payload['subject_type'] ?? null,
                'subject_id' => $payload['subject_id'] ?? null,
                'description' => $description,
                'before' => $payload['before'] ?? null,
                'after' => $payload['after'] ?? null,
                'undo' => $payload['undo'] ?? null,
            ]);
        } catch (QueryException) {
            return new self();
        }
    }
}
