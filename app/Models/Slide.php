<?php

namespace App\Models;

use App\Models\Concerns\HasUserScopeThroughOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Slide extends Model
{
    use HasUserScopeThroughOwner;

    protected $fillable = [
        'deck_id',
        'title',
        'notes',
        'layout',
        'sort_order',
        'canvas_state',
        'revision',
    ];

    protected function casts(): array
    {
        return [
            'canvas_state' => 'array',
            'revision' => 'integer',
        ];
    }

    public function deck(): BelongsTo
    {
        return $this->belongsTo(Deck::class);
    }

    public function elements(): HasMany
    {
        return $this->hasMany(Element::class);
    }

    protected static function userOwnershipRelation(): string
    {
        return 'deck.project';
    }
}
