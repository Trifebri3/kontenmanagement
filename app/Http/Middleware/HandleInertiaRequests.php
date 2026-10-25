<?php

namespace App\Http\Middleware;

use App\Services\WorkspaceContext;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $currentWorkspace = null;
        $workspaces = [];

        if ($user) {
            $context = app(WorkspaceContext::class);
            $workspace = $context->current();

            if ($workspace) {
                $role = $context->currentRole();
                $currentWorkspace = [
                    'id' => $workspace->id,
                    'name' => $workspace->name,
                    'slug' => $workspace->slug,
                    'logo_path' => $workspace->logo_path,
                    'timezone' => $workspace->timezone,
                    'role' => $role?->value,
                    'role_label' => $role?->label(),
                    'role_badge' => $role?->badgeColor(),
                    'can_manage_workspace' => $role?->canManageWorkspace() ?? false,
                    'can_manage_members' => $role?->canManageMembers() ?? false,
                ];
            }

            // Workspaces list for switcher
            $workspaces = $user->workspaces()
                ->select(['workspaces.id', 'workspaces.name', 'workspaces.slug', 'workspaces.logo_path'])
                ->get()
                ->map(fn ($w) => [
                    'id' => $w->id,
                    'name' => $w->name,
                    'slug' => $w->slug,
                    'role' => $w->pivot?->role,
                    'is_current' => $currentWorkspace && $w->id === $currentWorkspace['id'],
                ]);
        }

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $user,
                'currentWorkspace' => $currentWorkspace,
                'workspaces' => $workspaces,
            ],
            'flash' => [
                'success' => $request->session()->get('success'),
                'error' => $request->session()->get('error'),
            ],
        ];
    }
}
