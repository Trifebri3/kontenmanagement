<?php

namespace App\Models\Scopes;

use App\Services\WorkspaceContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

class WorkspaceScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        $context = app(WorkspaceContext::class);
        $workspaceId = $context->id();

        if ($workspaceId) {
            $builder->where($model->getTable().'.workspace_id', $workspaceId);
        }
    }
}
