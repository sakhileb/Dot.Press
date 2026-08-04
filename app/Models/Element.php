<?php

namespace App\Models;

use App\Models\Concerns\HasUserScopeThroughOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Element extends Model
{
    use HasUserScopeThroughOwner;

    protected $fillable = [
        'slide_id',
        'type',
        'name',
        'content',
        'style',
        'transform',
        'sort_order',
        'locked',
    ];

    protected function casts(): array
    {
        return [
            'content' => 'array',
            'style' => 'array',
            'transform' => 'array',
            'locked' => 'boolean',
        ];
    }

    public function slide(): BelongsTo
    {
        return $this->belongsTo(Slide::class);
    }

    protected static function userOwnershipRelation(): string
    {
        return 'slide.deck.project';
    }
}
