<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Deck extends Model
{
    protected $fillable = [
        'project_id',
        'title',
        'theme',
        'sort_order',
        'is_template',
    ];

    protected function casts(): array
    {
        return [
            'theme' => 'array',
            'is_template' => 'boolean',
        ];
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function slides(): HasMany
    {
        return $this->hasMany(Slide::class);
    }
}
