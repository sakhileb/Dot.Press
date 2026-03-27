<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiUsageLog extends Model
{
    protected $fillable = [
        'user_id',
        'deck_id',
        'slide_id',
        'action',
        'provider',
        'model',
        'safety_blocked',
        'safety_reason',
        'status',
        'input_tokens',
        'output_tokens',
        'latency_ms',
        'prompt',
        'response',
        'error_message',
        'meta',
    ];

    protected function casts(): array
    {
        return [
            'safety_blocked' => 'boolean',
            'meta' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }
}
