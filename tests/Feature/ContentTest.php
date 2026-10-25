<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\ContentFootage;
use App\Models\ContentItem;
use App\Models\ContentTodo;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setupUserAndWorkspace(): array
    {
        $user = User::factory()->create();
        $colleague = User::factory()->create();

        $workspace = Workspace::create([
            'name' => 'Aren Lestari',
            'owner_id' => $user->id,
        ]);

        $workspace->users()->attach($user->id, ['role' => WorkspaceRole::Admin->value]);
        $workspace->users()->attach($colleague->id, ['role' => WorkspaceRole::Member->value]);

        return [$user, $colleague, $workspace];
    }

    public function test_user_can_create_content_item_with_default_footages(): void
    {
        [$user, $colleague, $workspace] = $this->setupUserAndWorkspace();

        session(['current_workspace_id' => $workspace->id]);

        $response = $this->actingAs($user)->post(route('contents.store'), [
            'title' => 'Video Reel Panen Nira',
            'channel' => 'tiktok',
            'status' => 'shooting',
            'hook' => 'Air pohon disulap jadi gula aren!',
            'concept' => 'Video dokumentasi panen',
            'assignee_id' => $colleague->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_items', [
            'workspace_id' => $workspace->id,
            'title' => 'Video Reel Panen Nira',
            'channel' => 'tiktok',
            'status' => 'shooting',
            'assignee_id' => $colleague->id,
        ]);

        $content = ContentItem::where('title', 'Video Reel Panen Nira')->first();
        $this->assertNotNull($content);
        $this->assertGreaterThan(0, $content->footages()->count());
        $this->assertGreaterThan(0, $content->todos()->count());
    }

    public function test_user_can_toggle_footage_captured_status(): void
    {
        [$user, $colleague, $workspace] = $this->setupUserAndWorkspace();

        session(['current_workspace_id' => $workspace->id]);

        $content = ContentItem::create([
            'workspace_id' => $workspace->id,
            'title' => 'Eksperimen Gula Aren',
            'channel' => 'instagram',
            'status' => 'shooting',
            'created_by' => $user->id,
        ]);

        $footage = ContentFootage::create([
            'content_item_id' => $content->id,
            'title' => 'Shot 1: Close up tetesan',
            'is_captured' => false,
        ]);

        $response = $this->actingAs($colleague)->patch(route('footages.toggle', $footage->id));

        $response->assertRedirect();
        $this->assertTrue($footage->fresh()->is_captured);

        // Toggle back
        $this->actingAs($colleague)->patch(route('footages.toggle', $footage->id));
        $this->assertFalse($footage->fresh()->is_captured);
    }

    public function test_user_can_add_custom_footage_to_content(): void
    {
        [$user, $colleague, $workspace] = $this->setupUserAndWorkspace();

        session(['current_workspace_id' => $workspace->id]);

        $content = ContentItem::create([
            'workspace_id' => $workspace->id,
            'title' => 'Resep Kopi Aren',
            'channel' => 'tiktok',
            'status' => 'shooting',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($colleague)->post(route('contents.footages.store', $content->id), [
            'title' => 'Shot Drone Tambahan',
            'shot_type' => 'drone',
            'visual_notes' => 'Tampak atas kebun 4K',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('content_footages', [
            'content_item_id' => $content->id,
            'title' => 'Shot Drone Tambahan',
            'shot_type' => 'drone',
        ]);
    }

    public function test_user_can_toggle_and_add_todos(): void
    {
        [$user, $colleague, $workspace] = $this->setupUserAndWorkspace();

        session(['current_workspace_id' => $workspace->id]);

        $content = ContentItem::create([
            'workspace_id' => $workspace->id,
            'title' => 'Tutorial Pupuk',
            'channel' => 'youtube',
            'status' => 'editing',
            'created_by' => $user->id,
        ]);

        $todo = ContentTodo::create([
            'content_item_id' => $content->id,
            'title' => 'Export video 1080p',
            'is_completed' => false,
        ]);

        $this->actingAs($colleague)->patch(route('todos.toggle', $todo->id))->assertRedirect();
        $this->assertTrue($todo->fresh()->is_completed);

        $this->actingAs($colleague)->post(route('contents.todos.store', $content->id), [
            'title' => 'Upload thumbnail kustom',
        ])->assertRedirect();

        $this->assertDatabaseHas('content_todos', [
            'content_item_id' => $content->id,
            'title' => 'Upload thumbnail kustom',
        ]);
    }
}
