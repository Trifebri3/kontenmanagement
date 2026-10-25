<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\ContentFootage;
use App\Models\ContentItem;
use App\Models\ContentTodo;
use App\Services\WorkspaceContext;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function __construct(
        protected WorkspaceContext $workspaceContext
    ) {}

    /**
     * Store a newly created content item.
     */
    public function store(Request $request): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace) {
            abort(404, 'Workspace aktif tidak ditemukan.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'channel' => ['required', 'string', 'in:tiktok,instagram,youtube,facebook,linkedin'],
            'status' => ['required', 'string', 'in:idea,shooting,editing,review,ready,published'],
            'hook' => ['nullable', 'string'],
            'concept' => ['nullable', 'string'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'target_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $user = $request->user();

        // Generate simple unique code (e.g. CNT-04)
        $count = $workspace->contentItems()->count() + 1;
        $codePrefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $workspace->name), 0, 2)) ?: 'CT';
        $code = sprintf('%s-%02d', $codePrefix, $count);

        $content = $workspace->contentItems()->create([
            ...$validated,
            'code' => $code,
            'created_by' => $user->id,
        ]);

        // Automatically populate standard default footage checklist
        $content->footages()->createMany([
            ['title' => 'Shot 1: Hook pembuka 3 detik pertama (Visual gerak cepat)', 'visual_notes' => 'Orientasi 9:16 vertikal', 'shot_type' => 'medium', 'is_captured' => false, 'order' => 1],
            ['title' => 'Shot 2: Penjelasan / A-Roll narasumber bicara', 'visual_notes' => 'Audio mic jernih', 'shot_type' => 'medium', 'is_captured' => false, 'order' => 2],
            ['title' => 'Shot 3: Close-up B-Roll produk / proses kerja lapangan', 'visual_notes' => 'Detail angle menarik', 'shot_type' => 'close_up', 'is_captured' => false, 'order' => 3],
            ['title' => 'Shot 4: Call to action / Penutup', 'visual_notes' => 'Logo atau ajakan interaksi', 'shot_type' => 'medium', 'is_captured' => false, 'order' => 4],
        ]);

        // Default to-do checklist
        $content->todos()->createMany([
            ['title' => 'Shooting seluruh footage lapangan', 'is_completed' => false, 'order' => 1],
            ['title' => 'Editing video & pasang subtitle kontras', 'is_completed' => false, 'order' => 2],
            ['title' => 'Review bersama tim (tanpa hierarki)', 'is_completed' => false, 'order' => 3],
            ['title' => 'Jadwalkan di kalender konten', 'is_completed' => false, 'order' => 4],
        ]);

        ActivityLog::record(
            workspaceId: $workspace->id,
            actorId: $user->id,
            entityType: 'content_item',
            entityId: $content->id,
            action: 'created_content',
            after: ['title' => $content->title, 'code' => $code],
            ipAddress: $request->ip()
        );

        return redirect()->back()->with('success', "Konten '{$content->title}' berhasil dibuat.");
    }

    /**
     * Update content status, assignee, or notes.
     */
    public function update(Request $request, ContentItem $content): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $content->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'status' => ['sometimes', 'required', 'string', 'in:idea,shooting,editing,review,ready,published'],
            'assignee_id' => ['nullable', 'exists:users,id'],
            'hook' => ['nullable', 'string'],
            'concept' => ['nullable', 'string'],
            'target_date' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        $content->update($validated);

        return redirect()->back()->with('success', 'Konten berhasil diperbarui.');
    }

    /**
     * Toggle footage captured status.
     */
    public function toggleFootage(ContentFootage $footage): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $footage->contentItem->workspace_id !== $workspace->id) {
            abort(404);
        }

        $footage->update([
            'is_captured' => ! $footage->is_captured,
        ]);

        return redirect()->back();
    }

    /**
     * Add a footage shot to content item.
     */
    public function addFootage(Request $request, ContentItem $content): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $content->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'visual_notes' => ['nullable', 'string'],
            'shot_type' => ['nullable', 'string', 'in:wide,medium,close_up,macro,drone,pov'],
        ]);

        $order = $content->footages()->max('order') + 1;

        $content->footages()->create([
            ...$validated,
            'shot_type' => $validated['shot_type'] ?? 'medium',
            'order' => $order,
            'is_captured' => false,
        ]);

        return redirect()->back()->with('success', 'Shot footage berhasil ditambahkan.');
    }

    /**
     * Delete footage shot.
     */
    public function destroyFootage(ContentFootage $footage): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $footage->contentItem->workspace_id !== $workspace->id) {
            abort(404);
        }

        $footage->delete();

        return redirect()->back()->with('success', 'Shot footage dihapus.');
    }

    /**
     * Toggle todo task item.
     */
    public function toggleTodo(ContentTodo $todo): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $todo->contentItem->workspace_id !== $workspace->id) {
            abort(404);
        }

        $todo->update([
            'is_completed' => ! $todo->is_completed,
        ]);

        return redirect()->back();
    }

    /**
     * Add a todo task item.
     */
    public function addTodo(Request $request, ContentItem $content): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $content->workspace_id !== $workspace->id) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
        ]);

        $order = $content->todos()->max('order') + 1;

        $content->todos()->create([
            'title' => $validated['title'],
            'order' => $order,
            'is_completed' => false,
        ]);

        return redirect()->back()->with('success', 'To-do berhasil ditambahkan.');
    }

    /**
     * Delete todo task item.
     */
    public function destroyTodo(ContentTodo $todo): RedirectResponse
    {
        $workspace = $this->workspaceContext->current();
        if (! $workspace || $todo->contentItem->workspace_id !== $workspace->id) {
            abort(404);
        }

        $todo->delete();

        return redirect()->back()->with('success', 'To-do dihapus.');
    }
}
