<?php

namespace App\Http\Middleware;

use App\Models\Workspace;
use App\Services\WorkspaceContext;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureWorkspaceContext
{
    public function __construct(
        protected WorkspaceContext $workspaceContext
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        // If route has workspace route parameter (model binding or slug/id)
        $routeWorkspace = $request->route('workspace');
        if ($routeWorkspace) {
            if (! ($routeWorkspace instanceof Workspace)) {
                $routeWorkspace = Workspace::where('id', $routeWorkspace)
                    ->orWhere('slug', $routeWorkspace)
                    ->firstOrFail();
            }

            if (! $user->isMemberOf($routeWorkspace)) {
                abort(403, 'Anda tidak memiliki akses ke workspace ini.');
            }

            $this->workspaceContext->switch($routeWorkspace);
        } else {
            // Ensure context is resolved or initialized
            $this->workspaceContext->current();
        }

        return $next($request);
    }
}
