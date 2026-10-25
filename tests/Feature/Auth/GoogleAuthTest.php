<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\Workspace;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\Two\User as SocialiteUser;
use Mockery;
use Tests\TestCase;

class GoogleAuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_google_redirect_without_credentials_flashes_error(): void
    {
        config([
            'services.google.client_id' => null,
            'services.google.client_secret' => null,
        ]);

        $response = $this->get(route('auth.google.redirect'));

        $response->assertRedirect(route('login'));
        $response->assertSessionHas('error');
    }

    public function test_google_redirect_with_credentials_redirects_to_google(): void
    {
        config([
            'services.google.client_id' => 'fake-client-id',
            'services.google.client_secret' => 'fake-client-secret',
        ]);

        $response = $this->get(route('auth.google.redirect'));

        // Socialite redirects to accounts.google.com
        $response->assertRedirect();
        $this->assertStringContainsString('accounts.google.com', $response->headers->get('Location'));
    }

    public function test_google_callback_creates_user_and_default_workspace(): void
    {
        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-unique-id-12345');
        $abstractUser->shouldReceive('getEmail')->andReturn('creators@perusahaan.com');
        $abstractUser->shouldReceive('getName')->andReturn('Kreator Konten');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://lh3.googleusercontent.com/avatar.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticated();

        $user = User::where('email', 'creators@perusahaan.com')->first();
        $this->assertNotNull($user);
        $this->assertEquals('google-unique-id-12345', $user->google_id);

        // Check workspace was automatically provisioned
        $this->assertGreaterThan(0, $user->workspaces()->count());
    }

    public function test_google_callback_logs_in_existing_user(): void
    {
        $existing = User::factory()->create([
            'email' => 'existing@perusahaan.com',
            'name' => 'Existing User',
        ]);

        $workspace = Workspace::create([
            'name' => 'Existing Workspace',
            'owner_id' => $existing->id,
        ]);
        $workspace->users()->attach($existing->id, ['role' => 'admin']);

        $abstractUser = Mockery::mock(SocialiteUser::class);
        $abstractUser->shouldReceive('getId')->andReturn('google-id-existing');
        $abstractUser->shouldReceive('getEmail')->andReturn('existing@perusahaan.com');
        $abstractUser->shouldReceive('getName')->andReturn('Existing User');
        $abstractUser->shouldReceive('getAvatar')->andReturn('https://avatar.google.com/pic.jpg');

        $provider = Mockery::mock('Laravel\Socialite\Two\GoogleProvider');
        $provider->shouldReceive('user')->andReturn($abstractUser);

        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $response = $this->get(route('auth.google.callback'));

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($existing);
        $this->assertEquals('google-id-existing', $existing->fresh()->google_id);
    }
}
