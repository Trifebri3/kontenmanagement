<?php

use App\Http\Controllers\ContentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WorkspaceController;
use App\Http\Controllers\WorkspaceMemberController;
use App\Services\WorkspaceContext;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        $context = app(WorkspaceContext::class);
        $workspace = $context->current();

        $workspaceMembers = $workspace
            ? $workspace->members()
                ->with('user:id,name,email,avatar_path')
                ->get()
                ->map(fn ($m) => [
                    'id' => $m->user?->id,
                    'name' => $m->user?->name ?? 'Anggota',
                    'email' => $m->user?->email,
                    'role' => $m->role->label(),
                ])
                ->values()
                ->all()
            : [];

        $contents = $workspace
            ? $workspace->contentItems()
                ->with(['assignee:id,name,email,avatar_path', 'creator:id,name', 'footages', 'todos'])
                ->latest('created_at')
                ->get()
                ->map(fn ($item) => [
                    'id' => $item->id,
                    'code' => $item->code,
                    'title' => $item->title,
                    'channel' => $item->channel,
                    'status' => $item->status,
                    'status_label' => $item->statusBadge()['label'],
                    'status_color' => $item->statusBadge()['color'],
                    'hook' => $item->hook,
                    'concept' => $item->concept,
                    'notes' => $item->notes,
                    'target_date' => $item->target_date?->format('d M Y, H:i'),
                    'assignee' => $item->assignee ? [
                        'id' => $item->assignee->id,
                        'name' => $item->assignee->name,
                        'email' => $item->assignee->email,
                    ] : null,
                    'footages_count' => $item->footages->count(),
                    'captured_footages_count' => $item->footages->where('is_captured', true)->count(),
                    'todos_count' => $item->todos->count(),
                    'completed_todos_count' => $item->todos->where('is_completed', true)->count(),
                    'footages' => $item->footages->map(fn ($f) => [
                        'id' => $f->id,
                        'title' => $f->title,
                        'visual_notes' => $f->visual_notes,
                        'shot_type' => $f->shot_type,
                        'is_captured' => (bool) $f->is_captured,
                    ])->values()->all(),
                    'todos' => $item->todos->map(fn ($t) => [
                        'id' => $t->id,
                        'title' => $t->title,
                        'is_completed' => (bool) $t->is_completed,
                    ])->values()->all(),
                ])
                ->values()
                ->all()
            : [];

        $recentActivities = $workspace
            ? $workspace->activityLogs()
                ->with('actor:id,name,avatar_path')
                ->latest('created_at')
                ->take(10)
                ->get()
                ->map(fn ($log) => [
                    'id' => $log->id,
                    'actor' => $log->actor?->name ?? 'System',
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'created_at' => $log->created_at->diffForHumans(),
                ])
                ->values()
                ->all()
            : [];

        return Inertia::render('Dashboard', [
            'workspaceMembers' => $workspaceMembers,
            'contents' => $contents,
            'recentActivities' => $recentActivities,
        ]);
    })->name('dashboard');

    // Contents, Footages & Todos (Per-Konten Operations)
    Route::post('/contents', [ContentController::class, 'store'])->name('contents.store');
    Route::patch('/contents/{content}', [ContentController::class, 'update'])->name('contents.update');
    Route::post('/contents/{content}/footages', [ContentController::class, 'addFootage'])->name('contents.footages.store');
    Route::patch('/footages/{footage}/toggle', [ContentController::class, 'toggleFootage'])->name('footages.toggle');
    Route::delete('/footages/{footage}', [ContentController::class, 'destroyFootage'])->name('footages.destroy');
    Route::post('/contents/{content}/todos', [ContentController::class, 'addTodo'])->name('contents.todos.store');
    Route::patch('/todos/{todo}/toggle', [ContentController::class, 'toggleTodo'])->name('todos.toggle');
    Route::delete('/todos/{todo}', [ContentController::class, 'destroyTodo'])->name('todos.destroy');

    // Workspaces
    Route::get('/workspaces', [WorkspaceController::class, 'index'])->name('workspaces.index');
    Route::post('/workspaces', [WorkspaceController::class, 'store'])->name('workspaces.store');
    Route::post('/workspaces/{workspace}/switch', [WorkspaceController::class, 'switch'])->name('workspaces.switch');
    Route::get('/workspaces/settings', [WorkspaceController::class, 'settings'])->name('workspaces.settings');
    Route::patch('/workspaces/{workspace}', [WorkspaceController::class, 'update'])->name('workspaces.update');

    // Workspace Members & Team
    Route::get('/workspaces/members', [WorkspaceMemberController::class, 'index'])->name('workspaces.members.index');
    Route::post('/workspaces/members/invite', [WorkspaceMemberController::class, 'invite'])->name('workspaces.members.invite');
    Route::patch('/workspaces/members/{member}/role', [WorkspaceMemberController::class, 'updateRole'])->name('workspaces.members.update-role');
    Route::delete('/workspaces/members/{member}', [WorkspaceMemberController::class, 'destroy'])->name('workspaces.members.destroy');

    // Profile
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
