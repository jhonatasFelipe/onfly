<?php

declare(strict_types=1);

namespace Tests\Feature\Http\TravelOrder;

use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Notifications\TravelOrderApprovedNotification;
use App\Notifications\TravelOrderCancelledNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

final class TravelOrderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_travel_order(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $response = $this->postJson('/api/v1/travel-orders', [
            'destination' => 'Salvador',
            'departure_date' => now()->addDay()->toDateString(),
            'return_date' => now()->addDays(5)->toDateString(),
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.destination', 'Salvador')
            ->assertJsonPath('data.status', 'solicitado');

        $this->assertDatabaseHas('travel_orders', [
            'user_id' => $user->id,
            'destination' => 'Salvador',
        ]);
    }

    public function test_user_cannot_create_travel_order_with_invalid_dates(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/travel-orders', [
            'destination' => 'Salvador',
            'departure_date' => now()->addDays(5)->toDateString(),
            'return_date' => now()->addDay()->toDateString(),
        ])->assertUnprocessable();
    }

    public function test_user_cannot_create_travel_order_without_destination(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->postJson('/api/v1/travel-orders', [
            'departure_date' => now()->addDay()->toDateString(),
            'return_date' => now()->addDays(5)->toDateString(),
        ])->assertUnprocessable();
    }

    public function test_user_can_list_own_travel_orders(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->count(2)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'per_page', 'total', 'last_page'],
            ])
            ->assertJsonPath('meta.total', 2);
    }

    public function test_user_can_paginate_travel_orders(): void
    {
        $user = UserModel::factory()->create();
        TravelOrderModel::factory()->count(5)->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders?page=2&per_page=2')
            ->assertOk()
            ->assertJsonCount(2, 'data')
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.per_page', 2)
            ->assertJsonPath('meta.total', 5)
            ->assertJsonPath('meta.last_page', 3);
    }

    public function test_list_rejects_per_page_above_maximum(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $maxPerPage = (int) config('travel-orders.pagination.max_per_page');

        $this->getJson('/api/v1/travel-orders?per_page='.($maxPerPage + 1))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['per_page']);
    }

    public function test_list_rejects_invalid_created_date_range(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders?'.http_build_query([
            'created_from' => '2026-08-10',
            'created_to' => '2026-08-01',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['created_to']);
    }

    public function test_list_rejects_invalid_departure_date_range(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders?'.http_build_query([
            'departure_from' => '2026-09-10',
            'departure_to' => '2026-09-01',
        ]))
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['departure_to']);
    }

    public function test_user_can_view_own_travel_order(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrderModel::factory()->create(['user_id' => $user->id]);
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders/'.$order->id)
            ->assertOk()
            ->assertJsonPath('data.id', $order->id);
    }

    public function test_user_cannot_view_other_users_order(): void
    {
        $owner = UserModel::factory()->create();
        $other = UserModel::factory()->create();
        $order = TravelOrderModel::factory()->create(['user_id' => $owner->id]);

        Sanctum::actingAs($other);

        $this->getJson('/api/v1/travel-orders/'.$order->id)
            ->assertForbidden();
    }

    public function test_show_returns_not_found_for_missing_order(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders/550e8400-e29b-41d4-a716-446655440000')
            ->assertNotFound();
    }

    public function test_update_status_returns_not_found_for_missing_order(): void
    {
        $admin = UserModel::factory()->admin()->create();
        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/travel-orders/550e8400-e29b-41d4-a716-446655440000/status', [
            'status' => 'aprovado',
        ])->assertNotFound();
    }

    public function test_admin_can_approve_order_and_notification_is_sent(): void
    {
        Notification::fake();

        $user = UserModel::factory()->create();
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/travel-orders/'.$order->id.'/status', [
            'status' => 'aprovado',
        ])->assertOk()->assertJsonPath('data.status', 'aprovado');

        Notification::assertSentTo($user, TravelOrderApprovedNotification::class);
    }

    public function test_admin_can_cancel_solicitado_order(): void
    {
        Notification::fake();

        $user = UserModel::factory()->create();
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/travel-orders/'.$order->id.'/status', [
            'status' => 'cancelado',
        ])->assertOk()->assertJsonPath('data.status', 'cancelado');

        Notification::assertSentTo($user, TravelOrderCancelledNotification::class);
    }

    public function test_admin_cannot_cancel_approved_order(): void
    {
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->approved()->create();

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/travel-orders/'.$order->id.'/status', [
            'status' => 'cancelado',
        ])->assertStatus(409);
    }

    public function test_admin_cannot_revert_to_solicitado_status(): void
    {
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->create();

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/travel-orders/'.$order->id.'/status', [
            'status' => 'solicitado',
        ])->assertUnprocessable()
            ->assertJsonValidationErrors(['status']);
    }

    public function test_update_status_rejects_invalid_status_value(): void
    {
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->create();

        Sanctum::actingAs($admin);

        $this->patchJson('/api/v1/travel-orders/'.$order->id.'/status', [
            'status' => 'invalid',
        ])->assertUnprocessable();
    }

    public function test_list_rejects_invalid_status_filter(): void
    {
        $user = UserModel::factory()->create();
        Sanctum::actingAs($user);

        $this->getJson('/api/v1/travel-orders?status=invalid')
            ->assertUnprocessable();
    }

    public function test_regular_user_cannot_update_status(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrderModel::factory()->create(['user_id' => $user->id]);

        Sanctum::actingAs($user);

        $this->patchJson('/api/v1/travel-orders/'.$order->id.'/status', [
            'status' => 'aprovado',
        ])->assertForbidden();
    }

    public function test_guest_cannot_access_travel_orders(): void
    {
        $this->getJson('/api/v1/travel-orders')->assertUnauthorized();
    }
}
