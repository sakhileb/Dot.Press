<?php

namespace App\Models;

use App\Models\Concerns\HasUserScopeThroughOwner;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Element extends Model
{
    use HasUserScopeThroughOwner;

    /**
     * Canvas (Konva) attributes that describe an element's position/size,
     * normalized into the `transform` column.
     */
    private const TRANSFORM_KEYS = ['x', 'y', 'width', 'height', 'rotation', 'scaleX', 'scaleY'];

    /**
     * Canvas attributes that describe an element's appearance, normalized
     * into the `style` column.
     */
    private const STYLE_KEYS = [
        'fill', 'stroke', 'strokeWidth', 'opacity', 'cornerRadius',
        'shadowColor', 'shadowBlur', 'shadowOffsetX', 'shadowOffsetY',
        'fontFamily', 'fontSize', 'fontStyle', 'fontWeight', 'textDecoration',
        'align', 'lineHeight',
    ];

    /**
     * Top-level keys handled separately (not part of transform/style/content).
     */
    private const RESERVED_KEYS = ['id', 'type', 'name', 'locked', 'sort_order'];

    protected $fillable = [
        'slide_id',
        'client_id',
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

    /**
     * Split a flat canvas element (as sent by the Konva frontend, e.g.
     * ['id' => 'el-1', 'type' => 'rect', 'x' => 100, 'fill' => '#fff'])
     * into this model's normalized column shape.
     *
     * @param  array<string, mixed>  $canvasElement
     * @return array{client_id: ?string, type: string, name: ?string, locked: bool, transform: array, style: array, content: array}
     */
    public static function attributesFromCanvasElement(array $canvasElement): array
    {
        $transform = [];
        $style = [];
        $content = [];

        foreach ($canvasElement as $key => $value) {
            if (in_array($key, self::RESERVED_KEYS, true)) {
                continue;
            }

            if (in_array($key, self::TRANSFORM_KEYS, true)) {
                $transform[$key] = $value;
            } elseif (in_array($key, self::STYLE_KEYS, true)) {
                $style[$key] = $value;
            } else {
                $content[$key] = $value;
            }
        }

        return [
            'client_id' => $canvasElement['id'] ?? null,
            'type' => $canvasElement['type'] ?? 'shape',
            'name' => $canvasElement['name'] ?? null,
            'locked' => (bool) ($canvasElement['locked'] ?? false),
            'transform' => $transform,
            'style' => $style,
            'content' => $content,
        ];
    }

    /**
     * Flatten this element back into the canvas (Konva) shape the frontend
     * reads/writes -- the inverse of attributesFromCanvasElement().
     *
     * @return array<string, mixed>
     */
    public function toCanvasElement(): array
    {
        return array_merge(
            [
                'id' => $this->client_id,
                'type' => $this->type,
            ],
            $this->transform ?? [],
            $this->style ?? [],
            $this->content ?? [],
            array_filter([
                'name' => $this->name,
                'locked' => $this->locked ? true : null,
            ], fn ($value) => $value !== null),
        );
    }
}
