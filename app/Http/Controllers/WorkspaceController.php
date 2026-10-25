<?php

namespace App\Http\Controllers;

use App\Enums\MemberStatus;
use App\Enums\WorkspaceRole;
use App\Models\ActivityLog;
use App\Models\Workspace;
use App\Services\WorkspaceContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class WorkspaceController extends Controller
{
    public function __construct(
        protected WorkspaceContext $workspaceContext
    ) {}

    /**
     * Display a listing of user's workspaces.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();

        $workspaces = $user->workspaces()
            ->with('owner:id,name,email')
            ->withCount(['members'])
            ->get()
            ->map(fn ($w) => [
                'id' => $w->id,
                'name' => $w->name,
                'slug' => $w->slug,
                'logo_path' => $w->logo_path,
                'timezone' => $w->timezone,
                'role' => $w->pivot?->role,
                'role_label' => WorkspaceRole::tryFrom($w->pivot?->role)?->label() ?? $w->pivot?->role,
                'is_owner' => $w->owner_id === $user->id,
                'owner' => $w->owner,
                'members_count' => $w->members_count,
                'created_at' => $w->created_at->format('d M Y'),
                'is_current' => $w->id === $this->workspaceContext->id(),
            ]);

        return Inertia::render('Workspaces/Index', [
            'workspaces' => $workspaces,
        ]);
    }

    /**
     * Store a newly created workspace.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'timezone' => ['nullable', 'string', 'max:64'],
        ]);

        $baseSlug = Str::slug($validated['name']);
        $slug = $baseSlug ?: 'workspace';
        $counter = 1;
        while (Workspace::where('slug', $slug)->exists()) {
            $slug = ($baseSlug ?: 'workspace').'-'.$counter;
            $counter++;
        }

        $workspace = Workspace::create([
            'name' => $validated['name'],
            'slug' => $slug,
            'owner_id' => $user->id,
            'timezone' => $validated['timezone'] ?? 'Asia/Jakarta',
        ]);

        $workspace->members()->create([
            'user_id' => $user->id,
            'role' => WorkspaceRole::Owner,
            'status' => MemberStatus::Active,
            'joined_at' => now(),
        ]);

        ActivityLog::record(
            workspaceId: $workspace->id,
            actorId: $user->id,
            entityType: 'workspace',
            entityId: $workspace->id,
            action: 'created',
            after: ['name' => $workspace->name],
            ipAddress: $request->ip()
        );

        $this->workspaceContext->switch($workspace);

        return redirect()->route('dashboard')->with('success', "Workspace '{$workspace->name}' berhasil dibuat!");
    }

    /**
     * Switch active workspace.
     */
    public function switch(Request $request, Workspace $workspace): RedirectResponse
    {
        $user = $request->user();

        if (! $user->isMemberOf($workspace)) {
            abort(403, 'Anda tidak memiliki akses ke workspace ini.');
        }

        $this->workspaceContext->switch($workspace);

        return redirect()->back()->with('success', "Beralih ke workspace '{$workspace->name}'.");
    }

    /**
     * Display workspace settings.
     */
    public function settings(Request $request): Response
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace) {
            abort(404, 'Workspace aktif tidak ditemukan.');
        }

        Gate::authorize('update', $workspace);

        return Inertia::render('Workspaces/Settings', [
            'workspace' => [
                'id' => $workspace->id,
                'name' => $workspace->name,
                'slug' => $workspace->slug,
                'timezone' => $workspace->timezone,
                'logo_path' => $workspace->logo_path,
                'created_at' => $workspace->created_at->format('d M Y'),
                'owner' => $workspace->owner?->only(['id', 'name', 'email']),
            ],
            'timezones' => [
                'Asia/Jakarta' => 'WIB (Asia/Jakarta - UTC+7)',
                'Asia/Makassar' => 'WITA (Asia/Makassar - UTC+8)',
                'Asia/Jayapura' => 'WIT (Asia/Jayapura - UTC+9)',
                'UTC' => 'UTC',
            ],
        ]);
    }

    /**
     * Update workspace settings.
     */
    public function update(Request $request, Workspace $workspace): RedirectResponse
    {
        Gate::authorize('update', $workspace);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'timezone' => ['required', 'string', 'max:64'],
        ]);

        $before = [
            'name' => $workspace->name,
            'timezone' => $workspace->timezone,
        ];

        $workspace->update($validated);

        ActivityLog::record(
            workspaceId: $workspace->id,
            actorId: $request->user()->id,
            entityType: 'workspace',
            entityId: $workspace->id,
            action: 'updated_settings',
            before: $before,
            after: $validated,
            ipAddress: $request->ip()
        );

        return redirect()->back()->with('success', 'Pengaturan workspace berhasil diperbarui.');
    }
}
