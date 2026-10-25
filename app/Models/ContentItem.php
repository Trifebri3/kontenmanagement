<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ContentItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'workspace_id',
        'code',
        'title',
        'channel',
        'status',
        'hook',
        'concept',
        'assignee_id',
        'created_by',
        'target_date',
        'notes',
    ];

    protected $casts = [
        'target_date' => 'datetime',
    ];

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assignee_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function footages(): HasMany
    {
        return $this->hasMany(ContentFootage::class)->orderBy('order')->orderBy('id');
    }

    public function todos(): HasMany
    {
        return $this->hasMany(ContentTodo::class)->orderBy('order')->orderBy('id');
    }

    public function statusBadge(): array
    {
        return match ($this->status) {
            'idea' => ['label' => 'Ide Konten', 'color' => 'bg-purple-50 text-purple-700 border-purple-200'],
            'shooting' => ['label' => 'Syuting Lapangan', 'color' => 'bg-amber-50 text-amber-700 border-amber-200'],
            'editing' => ['label' => 'Editing Video', 'color' => 'bg-blue-50 text-blue-700 border-blue-200'],
            'review' => ['label' => 'Menunggu Review', 'color' => 'bg-indigo-50 text-indigo-700 border-indigo-200'],
            'ready' => ['label' => 'Siap Tayang', 'color' => 'bg-emerald-50 text-emerald-700 border-emerald-200'],
            'published' => ['label' => 'Sudah Tayang', 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
            default => ['label' => ucfirst($this->status), 'color' => 'bg-slate-100 text-slate-700 border-slate-200'],
        };
    }
}
