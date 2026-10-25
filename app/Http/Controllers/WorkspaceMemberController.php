<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Enums\WorkspaceRole;
use App\Models\ActivityLog;
use App\Models\User;
use App\Models\WorkspaceInvitation;
use App\Models\WorkspaceMember;
use App\Services\WorkspaceContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceMemberController extends Controller
{
    public function __construct(
        protected WorkspaceContext $workspaceContext
    ) {}

    /**
     * Display members of the active workspace.
     */
    public function index(): Response
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace) {
            abort(404, 'Workspace aktif tidak ditemukan.');
        }

        $user = auth()->user();
        $userRole = $workspace->roleOf($user);

        $members = $workspace->members()
            ->with('user:id,name,email,avatar_path,last_active_at')
            ->get()
            ->map(fn (WorkspaceMember $member) => [
                'id' => $member->id,
                'user_id' => $member->user_id,
                'name' => $member->user?->name,
                'email' => $member->user?->email,
                'avatar_path' => $member->user?->avatar_path,
                'role' => $member->role->value,
                'role_label' => $member->role->label(),
                'badge_color' => $member->role->badgeColor(),
                'status' => $member->status->value,
                'status_label' => $member->status->label(),
                'joined_at' => $member->joined_at?->format('d M Y H:i'),
                'is_owner' => $member->role === WorkspaceRole::Owner,
                'is_self' => $member->user_id === $user->id,
            ]);

        $invitations = $workspace->invitations()
            ->with('inviter:id,name')
            ->whereNull('accepted_at')
            ->where('expires_at', '>', now())
            ->get()
            ->map(fn (WorkspaceInvitation $invitation) => [
                'id' => $invitation->id,
                'email' => $invitation->email,
                'role' => $invitation->role->value,
                'role_label' => $invitation->role->label(),
                'invited_by' => $invitation->inviter?->name,
                'expires_at' => $invitation->expires_at->format('d M Y H:i'),
            ]);

        $availableRoles = [
            ['value' => WorkspaceRole::Member->value, 'label' => 'User'],
            ['value' => WorkspaceRole::Admin->value, 'label' => 'Admin'],
        ];

        return Inertia::render('Workspaces/Members', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
            ],
            'members' => $members,
            'invitations' => $invitations,
            'availableRoles' => $availableRoles,
            'canManage' => $userRole?->canManageMembers() ?? false,
        ]);
    }

    /**
     * Invite a new member to the active workspace.
     */
    public function invite(Request $request): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace) {
            abort(404);
        }

        $user = $request->user();
        if (! $workspace->roleOf($user)?->canManageMembers()) {
            abort(403, 'Anda tidak memiliki hak untuk mengundang anggota.');
        }

        $validated = $request->validate([
            'email' => ['required', 'email'],
            'role' => ['required', Rule::in([WorkspaceRole::Admin->value, WorkspaceRole::Member->value])],
        ]);

        $role = WorkspaceRole::from($validated['role']);

        // Check if user already in workspace
        $existingUser = User::where('email', $validated['email'])->first();
        if ($existingUser && $existingUser->isMemberOf($workspace)) {
            return redirect()->back()->with('error', 'Pengguna dengan email ini sudah menjadi anggota workspace.');
        }

        if ($existingUser) {
            // Add user directly as member
            $member = $workspace->members()->create([
                'user_id' => $existingUser->id,
                'role' => $role,
                'status' => MemberStatus::Active,
                'joined_at' => now(),
            ]);

            ActivityLog::record(
                workspaceId: $workspace->id,
                actorId: $user->id,
                entityType: 'workspace_member',
                entityId: $member->id,
                action: 'added_member',
                after: ['email' => $existingUser->email, 'role' => $role->value],
                ipAddress: $request->ip()
            );

            return redirect()->back()->with('success', "{$existingUser->name} ({$existingUser->email}) berhasil ditambahkan sebagai {$role->label()}.");
        }

        // Create invitation for pending registered user
        $token = Str::random(40);
        $invitation = $workspace->invitations()->create([
            'email' => $validated['email'],
            'role' => $role,
            'token_hash' => hash('sha256', $token),
            'invited_by' => $user->id,
            'expires_at' => now()->addDays(7),
        ]);

        ActivityLog::record(
            workspaceId: $workspace->id,
            actorId: $user->id,
            entityType: 'workspace_invitation',
            entityId: $invitation->id,
            action: 'created_invitation',
            after: ['email' => $validated['email'], 'role' => $role->value],
            ipAddress: $request->ip()
        );

        return redirect()->back()->with('success', "Undangan untuk {$validated['email']} berhasil dibuat (berlaku 7 hari).");
    }

    /**
     * Update member's role.
     */
    public function updateRole(Request $request, WorkspaceMember $member): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $member->workspace_id !== $workspace->id) {
            abort(404);
        }

        $user = $request->user();
        if (! $workspace->roleOf($user)?->canManageMembers()) {
            abort(403, 'Akses ditolak.');
        }

        if ($member->role === WorkspaceRole::Owner) {
            return redirect()->back()->with('error', 'Peran Workspace Owner tidak dapat diubah.');
        }

        $validated = $request->validate([
            'role' => ['required', Rule::in([WorkspaceRole::Admin->value, WorkspaceRole::Member->value])],
        ]);

        $newRole = WorkspaceRole::from($validated['role']);

        $oldRole = $member->role;
        $member->update(['role' => $newRole]);

        ActivityLog::record(
            workspaceId: $workspace->id,
            actorId: $user->id,
            entityType: 'workspace_member',
            entityId: $member->id,
            action: 'updated_role',
            before: ['role' => $oldRole->value],
            after: ['role' => $newRole->value],
            ipAddress: $request->ip()
        );

        return redirect()->back()->with('success', 'Peran anggota berhasil diperbarui.');
    }

    /**
     * Remove member from workspace.
     */
    public function destroy(Request $request, WorkspaceMember $member): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $member->workspace_id !== $workspace->id) {
            abort(404);
        }

        $user = $request->user();
        if (! $workspace->roleOf($user)?->canManageMembers()) {
            abort(403, 'Akses ditolak.');
        }

        if ($member->role === WorkspaceRole::Owner) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus Workspace Owner.');
        }

        $memberName = $member->user?->name ?? 'Anggota';
        $member->delete();

        ActivityLog::record(
            workspaceId: $workspace->id,
            actorId: $user->id,
            entityType: 'workspace_member',
            entityId: $member->id,
            action: 'removed_member',
            before: ['user_id' => $member->user_id, 'role' => $member->role->value],
            ipAddress: $request->ip()
        );

        return redirect()->back()->with('success', "{$memberName} telah dihapus dari workspace.");
    }
}
