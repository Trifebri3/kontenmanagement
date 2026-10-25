<?php

namespace App\Http\Controllers\Auth;

use App\Enums\MemberStatus;
use App\Enums\WorkspaceRole;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Workspace;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Throwable;

class GoogleAuthController extends Controller
{
    /**
     * Redirect the user to the Google authentication page.
     */
    public function redirect(): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');

        if (empty($clientId) || empty($clientSecret)) {
            return redirect()->route('login')->with(
                'error',
                'Login Google memerlukan GOOGLE_CLIENT_ID dan GOOGLE_CLIENT_SECRET pada file .env.'
            );
        }

        try {
            return Socialite::driver('google')->redirect();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Gagal menghubungkan ke layanan Google: '.$e->getMessage());
        }
    }

    /**
     * Obtain the user information from Google and log them in.
     */
    public function callback(): RedirectResponse
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (Throwable $e) {
            return redirect()->route('login')->with('error', 'Otentikasi Google dibatalkan atau terjadi kesalahan.');
        }

        // Find existing user by google_id or email
        $user = User::where('google_id', $googleUser->getId())
            ->orWhere('email', $googleUser->getEmail())
            ->first();

        if ($user) {
            // Update existing user's google_id or avatar if missing
            $user->update([
                'google_id' => $user->google_id ?: $googleUser->getId(),
                'avatar_path' => $user->avatar_path ?: $googleUser->getAvatar(),
            ]);
        } else {
            // Create new user from Google profile
            $user = User::create([
                'name' => $googleUser->getName() ?: 'User Google',
                'email' => $googleUser->getEmail(),
                'google_id' => $googleUser->getId(),
                'avatar_path' => $googleUser->getAvatar(),
                'email_verified_at' => now(),
                'password' => null,
            ]);

            // Create initial default workspace for newly registered Google user
            $workspaceName = 'Workspace '.explode(' ', $user->name)[0];
            $workspace = Workspace::create([
                'name' => $workspaceName,
                'owner_id' => $user->id,
                'timezone' => 'Asia/Jakarta',
            ]);

            $workspace->users()->attach($user->id, [
                'role' => WorkspaceRole::Owner->value,
                'status' => MemberStatus::Active->value,
                'joined_at' => now(),
            ]);
        }

        Auth::login($user, remember: true);

        // Ensure user has at least one active workspace set
        $currentWorkspace = $user->workspaces()->first() ?: Workspace::where('owner_id', $user->id)->first();
        if ($currentWorkspace) {
            session(['current_workspace_id' => $currentWorkspace->id]);
        }

        return redirect()->intended(route('dashboard'));
    }
}
