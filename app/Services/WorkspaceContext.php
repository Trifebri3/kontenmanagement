<?php

namespace App\Services;

use App\Enums\MemberStatus;
use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class WorkspaceContext
{
    protected ?Workspace $cachedWorkspace = null;

    public function current(): ?Workspace
    {
        if ($this->cachedWorkspace !== null) {
            return $this->cachedWorkspace;
        }

        /** @var User|null $user */
        $user = Auth::user();
        if (! $user) {
            return null;
        }

        $sessionWorkspaceId = Session::get('current_workspace_id');

        if ($sessionWorkspaceId) {
            $workspace = Workspace::find($sessionWorkspaceId);
            if ($workspace && $user->isMemberOf($workspace)) {
                $this->cachedWorkspace = $workspace;

                return $workspace;
            }
        }

        // Fallback to first accessible workspace (owned or member)
        $workspace = $user->ownedWorkspaces()->first()
            ?? $user->workspaces()->first();

        if ($workspace) {
            $this->switch($workspace);

            return $workspace;
        }

        // Auto-provision personal workspace for user if none exists
        $baseSlug = Str::slug($user->name.' Workspace');
        $slug = $baseSlug ?: 'personal-workspace';
        $counter = 1;
        while (Workspace::where('slug', $slug)->exists()) {
            $slug = ($baseSlug ?: 'personal-workspace').'-'.$counter;
            $counter++;
        }

        $workspace = Workspace::create([
            'name' => $user->name.' Workspace',
            'slug' => $slug,
            'owner_id' => $user->id,
            'timezone' => 'Asia/Jakarta',
        ]);

        $workspace->members()->create([
            'user_id' => $user->id,
            'role' => WorkspaceRole::Owner,
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);

        $this->switch($workspace);

        return $workspace;
    }

    public function id(): ?int
    {
        return $this->current()?->id;
    }

    public function switch(Workspace $workspace): void
    {
        Session::put('current_workspace_id', $workspace->id);
        $this->cachedWorkspace = $workspace;
    }

    public function currentRole(): ?WorkspaceRole
    {
        $workspace = $this->current();
        /** @var User|null $user */
        $user = Auth::user();

        if (! $workspace || ! $user) {
            return null;
        }

        return $workspace->roleOf($user);
    }

    public function isOwner(): bool
    {
        return $this->currentRole() === WorkspaceRole::Owner;
    }

    public function isAdminOrOwner(): bool
    {
        $role = $this->currentRole();

        return $role === WorkspaceRole::Owner || $role === WorkspaceRole::Admin;
    }

    public function can(string $ability): bool
    {
        $role = $this->currentRole();
        if (! $role) {
            return false;
        }

        return match ($ability) {
            'manage-workspace' => $role->canManageWorkspace(),
            'manage-members' => $role->canManageMembers(),
            'create-brief' => $role->canCreateBrief(),
            'approve-content' => $role->canApproveContent(),
            'publish-content' => $role->canPublish(),
            default => false,
        };
    }
}
