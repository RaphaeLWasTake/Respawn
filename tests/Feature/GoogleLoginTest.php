<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class GoogleLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_has_a_google_login_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('Masuk dengan Google')
            ->assertSee(route('google.redirect'));
    }

    public function test_register_page_offers_google_sign_up(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('Daftar dengan Google')
            ->assertSee(route('google.redirect'));
    }

    public function test_google_redirect_stores_state_and_redirects_to_google(): void
    {
        config([
            'services.google.client_id' => 'client-id',
            'services.google.redirect' => 'https://example.test/auth/google/callback',
        ]);

        $response = $this->get(route('google.redirect'));

        $response->assertRedirectContains('https://accounts.google.com/o/oauth2/v2/auth');
        $this->assertNotEmpty(session('google_oauth_state'));
    }

    public function test_google_callback_creates_and_authenticates_a_verified_user(): void
    {
        config([
            'services.google.client_id' => 'client-id',
            'services.google.client_secret' => 'client-secret',
            'services.google.redirect' => 'https://example.test/auth/google/callback',
        ]);

        Http::fake([
            'https://oauth2.googleapis.com/token' => Http::response(['access_token' => 'access-token']),
            'https://openidconnect.googleapis.com/v1/userinfo' => Http::response([
                'email' => 'google-player@example.test',
                'email_verified' => true,
                'name' => 'Google Player',
            ]),
        ]);

        $this->withSession(['google_oauth_state' => 'valid-state'])
            ->get(route('google.callback', ['state' => 'valid-state', 'code' => 'authorization-code']))
            ->assertRedirect(route('dashboard'));

        $user = User::where('email', 'google-player@example.test')->firstOrFail();
        $this->assertSame('Google Player', $user->name);
        $this->assertAuthenticatedAs($user);
    }

    public function test_google_callback_rejects_an_invalid_state(): void
    {
        Http::fake();

        $this->withSession(['google_oauth_state' => 'expected-state'])
            ->get(route('google.callback', ['state' => 'wrong-state', 'code' => 'authorization-code']))
            ->assertRedirect(route('login'))
            ->assertSessionHas('error');

        Http::assertNothingSent();
        $this->assertGuest();
    }
}
