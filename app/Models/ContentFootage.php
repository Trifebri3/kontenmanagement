<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentFootage extends Model
{
    use HasFactory;

    protected $fillable = [
        'content_item_id',
        'title',
        'visual_notes',
        'shot_type',
        'is_captured',
        'order',
    ];

    protected $casts = [
        'is_captured' => 'boolean',
        'order' => 'integer',
    ];

    public function contentItem(): BelongsTo
    {
        return $this->belongsTo(ContentItem::class);
    }
}
