<?php

namespace Tests\Feature;

use App\Enums\WorkspaceRole;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkspaceTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_their_workspaces(): void
    {
        $user = User::factory()->create();
        $workspace = Workspace::create([
            'name' => 'Aren Lestari',
            'owner_id' => $user->id,
            'timezone' => 'Asia/Jakarta',
        ]);
        $workspace->users()->attach($user->id, ['role' => WorkspaceRole::Owner->value]);

        $response = $this->actingAs($user)->get(route('workspaces.index'));

        $response->assertOk();
    }

    public function test_user_can_create_new_workspace_and_becomes_owner(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('workspaces.store'), [
            'name' => 'PT Bio Inovasi Nusantara',
            'timezone' => 'Asia/Jakarta',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertDatabaseHas('workspaces', [
            'name' => 'PT Bio Inovasi Nusantara',
            'owner_id' => $user->id,
        ]);

        $workspace = Workspace::where('name', 'PT Bio Inovasi Nusantara')->first();
        $this->assertNotNull($workspace);
        $this->assertTrue($user->isMemberOf($workspace));
        $this->assertEquals(WorkspaceRole::Owner, $workspace->roleOf($user));
    }

    public function test_user_can_switch_active_workspace(): void
    {
        $user = User::factory()->create();
        $ws1 = Workspace::create(['name' => 'Workspace One', 'owner_id' => $user->id]);
        $ws2 = Workspace::create(['name' => 'Workspace Two', 'owner_id' => $user->id]);

        $ws1->users()->attach($user->id, ['role' => WorkspaceRole::Owner->value]);
        $ws2->users()->attach($user->id, ['role' => WorkspaceRole::Owner->value]);

        $response = $this->actingAs($user)->post(route('workspaces.switch', $ws2->id));

        $response->assertSessionHas('current_workspace_id', $ws2->id);
    }

    public function test_user_cannot_switch_to_workspace_they_do_not_belong_to(): void
    {
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();

        $otherWorkspace = Workspace::create(['name' => 'Competitor Corp', 'owner_id' => $user2->id]);
        $otherWorkspace->users()->attach($user2->id, ['role' => WorkspaceRole::Owner->value]);

        $response = $this->actingAs($user1)->post(route('workspaces.switch', $otherWorkspace->id));

        $response->assertForbidden();
    }

    public function test_owner_can_invite_member_and_assign_role(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['name' => 'Aren Lestari', 'owner_id' => $owner->id]);
        $workspace->users()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);

        session(['current_workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->post(route('workspaces.members.invite'), [
            'email' => 'member_agent@example.com',
            'role' => WorkspaceRole::Member->value,
        ]);

        $response->assertSessionHas('success');
        $this->assertDatabaseHas('workspace_invitations', [
            'workspace_id' => $workspace->id,
            'email' => 'member_agent@example.com',
            'role' => WorkspaceRole::Member->value,
        ]);
    }

    public function test_member_user_cannot_invite_members(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();

        $workspace = Workspace::create(['name' => 'Aren Lestari', 'owner_id' => $owner->id]);
        $workspace->users()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);
        $workspace->users()->attach($memberUser->id, ['role' => WorkspaceRole::Member->value]);

        session(['current_workspace_id' => $workspace->id]);

        $response = $this->actingAs($memberUser)->post(route('workspaces.members.invite'), [
            'email' => 'someone@example.com',
            'role' => WorkspaceRole::Member->value,
        ]);

        $response->assertForbidden();
    }

    public function test_owner_can_access_workspace_settings(): void
    {
        $owner = User::factory()->create();
        $workspace = Workspace::create(['name' => 'Aren Lestari', 'owner_id' => $owner->id]);
        $workspace->users()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);

        session(['current_workspace_id' => $workspace->id]);

        $response = $this->actingAs($owner)->get(route('workspaces.settings'));

        $response->assertOk();
    }

    public function test_member_user_cannot_access_workspace_settings(): void
    {
        $owner = User::factory()->create();
        $memberUser = User::factory()->create();
        $workspace = Workspace::create(['name' => 'Aren Lestari', 'owner_id' => $owner->id]);
        $workspace->users()->attach($owner->id, ['role' => WorkspaceRole::Owner->value]);
        $workspace->users()->attach($memberUser->id, ['role' => WorkspaceRole::Member->value]);

        session(['current_workspace_id' => $workspace->id]);

        $response = $this->actingAs($memberUser)->get(route('workspaces.settings'));

        $response->assertForbidden();
    }
}
