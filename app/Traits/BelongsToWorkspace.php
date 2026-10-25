<?php

namespace App\Traits;

use App\Models\Scopes\WorkspaceScope;
use App\Models\Workspace;
use App\Services\WorkspaceContext;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

trait BelongsToWorkspace
{
    public static function bootBelongsToWorkspace(): void
    {
        static::addGlobalScope(new WorkspaceScope);

        static::creating(function ($model): void {
            if (empty($model->workspace_id)) {
                $context = app(WorkspaceContext::class);
                $workspaceId = $context->id();
                if ($workspaceId) {
                    $model->workspace_id = $workspaceId;
                }
            }
        });
    }

    public function workspace(): BelongsTo
    {
        return $this->belongsTo(Workspace::class);
    }
}
