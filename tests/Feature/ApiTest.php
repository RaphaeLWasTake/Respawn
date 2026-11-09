<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\SupportTicket;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Tests\TestCase;

class ApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_issues_a_bearer_token_and_logout_revokes_it(): void
    {
        $user = User::create([
            'name' => 'API User',
            'username' => 'api-user',
            'email' => 'api@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);

        $response = $this->postJson('/api/login', [
            'login' => $user->email,
            'password' => 'password123',
        ])->assertOk()
            ->assertJsonPath('token_type', 'Bearer');

        $token = $response->json('token');
        $tokenId = PersonalAccessToken::findToken($token)->id;
        $this->withToken($token)->getJson('/api/user')->assertOk()->assertJsonPath('id', $user->id);
        $this->withToken($token)->postJson('/api/logout')->assertOk();
        $this->assertDatabaseMissing('personal_access_tokens', ['id' => $tokenId]);
        $this->app['auth']->forgetGuards();
        $this->withToken($token)->getJson('/api/user')->assertUnauthorized();
    }

    public function test_admin_can_manage_users_games_and_package_stock(): void
    {
        $admin = User::create([
            'name' => 'Admin',
            'username' => 'api-admin',
            'email' => 'admin-api@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'sanctum');

        $this->postJson('/api/admin/users', [
            'name' => 'New Helper',
            'username' => 'new-helper',
            'email' => 'new-helper@example.test',
            'password' => 'password123',
            'role' => 'helper',
        ])->assertCreated()->assertJsonPath('data.role', 'helper');

        $gameResponse = $this->postJson('/api/admin/games', [
            'name' => 'Example Game',
            'slug' => 'example-game',
            'genre' => 'RPG',
            'icon' => 'E',
            'image' => 'images/games/example.jpg',
            'tag' => 'Example',
            'color' => 'blue',
            'currency' => 'Coin',
            'id_label' => 'Player ID',
            'region' => false,
            'packages' => [[
                'name' => '100 Coin',
                'amount' => 100,
                'price' => 15000,
                'stock' => 3,
            ]],
        ])->assertCreated();

        $gameId = $gameResponse->json('data.id');
        $packageId = $gameResponse->json('data.packages.0.id');

        $this->patchJson("/api/admin/games/{$gameId}/packages/{$packageId}", ['stock' => 0])
            ->assertOk()
            ->assertJsonPath('data.stock', 0);
        $this->getJson('/api/games/example-game')->assertOk();
        $this->deleteJson("/api/admin/games/{$gameId}/packages/{$packageId}")->assertOk();
        $this->deleteJson("/api/admin/games/{$gameId}")->assertOk();
        $this->assertDatabaseMissing('games', ['id' => $gameId]);
    }

    public function test_user_can_create_orders_and_tickets_and_helper_can_manage_tickets(): void
    {
        $user = User::create([
            'name' => 'Player',
            'username' => 'api-player',
            'email' => 'player-api@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);
        $game = Game::where('slug', 'free-fire')->firstOrFail();
        $package = $game->packages()->firstOrFail();
        $stockBefore = $package->stock;

        $this->actingAs($user, 'sanctum');
        $orderResponse = $this->postJson('/api/orders', [
            'game_slug' => $game->slug,
            'package_id' => $package->id,
            'player_id' => '1234567890',
            'payment_method' => 'QRIS',
        ])->assertCreated();
        $this->assertSame($stockBefore - 1, $package->fresh()->stock);
        $this->getJson('/api/orders')->assertOk()->assertJsonPath('data.0.id', $orderResponse->json('data.id'));

        $ticketResponse = $this->postJson('/api/tickets', [
            'subject' => 'Tanya order',
            'message' => 'Mohon cek pesanan saya.',
        ])->assertCreated();

        $helper = User::create([
            'name' => 'Helper',
            'username' => 'api-helper',
            'email' => 'helper-api@example.test',
            'password' => 'password123',
            'role' => 'helper',
        ]);
        $this->actingAs($helper, 'sanctum');
        $this->patchJson('/api/helper/tickets/'.$ticketResponse->json('data.id'), [
            'reply' => 'Kami bantu cek.',
            'status' => 'Sedang diproses',
        ])->assertOk()->assertJsonPath('data.status', 'Sedang diproses');

        $this->assertDatabaseHas('support_tickets', [
            'id' => $ticketResponse->json('data.id'),
            'user_id' => $user->id,
            'status' => 'Sedang diproses',
        ]);
    }

    public function test_non_admin_cannot_manage_users_and_users_only_read_their_own_data(): void
    {
        $user = User::create([
            'name' => 'Player',
            'username' => 'api-player-2',
            'email' => 'player2-api@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);
        $user->supportTickets()->create([
            'subject' => 'My ticket',
            'message' => 'Need help',
        ]);

        $this->actingAs($user, 'sanctum');
        $this->getJson('/api/admin/users')->assertForbidden();
        $this->getJson('/api/tickets')->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/helper/tickets')->assertForbidden();
    }
}
