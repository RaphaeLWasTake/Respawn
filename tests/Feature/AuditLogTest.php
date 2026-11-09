<?php

namespace Tests\Feature;

use App\Models\Game;
use App\Models\AuditLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuditLogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'audit',
            '--path' => 'database/migrations/audit',
            '--force' => true,
        ]);
    }

    public function test_user_can_only_view_their_own_order_and_admin_can_manage_order_status(): void
    {
        $user = User::create([
            'name' => 'Order Owner',
            'username' => 'order-owner',
            'email' => 'owner@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);
        $otherUser = User::create([
            'name' => 'Other User',
            'username' => 'other-user',
            'email' => 'other@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);
        $game = Game::where('slug', 'free-fire')->firstOrFail();
        $package = $game->packages()->firstOrFail();

        $this->actingAs($user, 'sanctum');
        $orderResponse = $this->postJson('/api/orders', [
            'game_slug' => $game->slug,
            'package_id' => $package->id,
            'player_id' => '1234567890',
            'payment_method' => 'QRIS',
        ])->assertCreated();
        $orderId = $orderResponse->json('data.id');

        $this->getJson('/api/orders/'.$orderId)->assertOk()->assertJsonPath('data.id', $orderId);
        $otherOrder = $otherUser->orders()->create([
            'game' => 'Free Fire',
            'package' => '50 Diamond',
            'player_id' => '0987654321',
            'amount' => 8000,
            'payment_method' => 'QRIS',
            'status' => 'Menunggu pembayaran',
        ]);
        $this->getJson('/api/orders/'.$otherOrder->id)->assertNotFound();

        $this->getJson('/api/admin/orders')->assertForbidden();
        $this->getJson('/api/admin/audit-logs')->assertForbidden();

        $admin = User::create([
            'name' => 'Admin',
            'username' => 'order-admin',
            'email' => 'order-admin@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $this->actingAs($admin, 'sanctum');

        $this->getJson('/api/admin/orders?status=Menunggu%20pembayaran')
            ->assertOk()
            ->assertJsonFragment(['id' => $orderId]);
        $this->patchJson('/api/admin/orders/'.$orderId, ['status' => 'Diproses'])
            ->assertOk()
            ->assertJsonPath('data.status', 'Diproses');
        $this->getJson('/api/admin/audit-logs?action=order.status_updated')
            ->assertOk()
            ->assertJsonPath('data.0.action', 'order.status_updated');

        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.status_updated',
            'actor_user_id' => $admin->id,
            'auditable_id' => $orderId,
        ], 'audit');
    }

    public function test_game_search_returns_a_paginated_result(): void
    {
        $this->getJson('/api/games?search=Free%20Fire&per_page=1')
            ->assertOk()
            ->assertJsonPath('data.0.slug', 'free-fire')
            ->assertJsonPath('per_page', 1);
    }

    public function test_web_order_creation_is_written_to_the_audit_database(): void
    {
        $user = User::create([
            'name' => 'Web Player',
            'username' => 'web-player',
            'email' => 'web-player@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);
        $game = Game::where('slug', 'free-fire')->firstOrFail();

        $this->actingAs($user)->post(route('orders.store', $game->slug), [
            'player_id' => '1234567890',
            'package' => 0,
            'payment_method' => 'QRIS',
        ])->assertRedirect(route('game.show', $game->slug));

        $order = $user->orders()->latest()->firstOrFail();
        $this->assertDatabaseHas('audit_logs', [
            'action' => 'order.created',
            'actor_user_id' => $user->id,
            'auditable_id' => $order->id,
        ], 'audit');
    }

    public function test_audit_log_page_is_available_to_admins_only(): void
    {
        $admin = User::create([
            'name' => 'Audit Admin',
            'username' => 'audit-admin',
            'email' => 'audit-admin@example.test',
            'password' => 'password123',
            'role' => 'admin',
        ]);
        $user = User::create([
            'name' => 'Audit User',
            'username' => 'audit-user',
            'email' => 'audit-user@example.test',
            'password' => 'password123',
            'role' => 'user',
        ]);
        AuditLog::create([
            'actor_user_id' => $admin->id,
            'action' => 'order.created',
            'auditable_type' => 'App\\Models\\Order',
            'auditable_id' => 1,
            'changes' => ['status' => 'Menunggu pembayaran'],
            'created_at' => now(),
        ]);

        $this->get(route('admin.audit-logs'))->assertRedirect(route('login'));
        $this->actingAs($user)->get(route('admin.audit-logs'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.audit-logs'))
            ->assertOk()
            ->assertSee('Riwayat audit')
            ->assertSee('order.created')
            ->assertSee('Menunggu pembayaran');
    }
}
