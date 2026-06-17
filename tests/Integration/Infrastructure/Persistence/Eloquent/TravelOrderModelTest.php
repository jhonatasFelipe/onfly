<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Persistence\Eloquent;

use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TravelOrderModelTest extends TestCase
{
    use RefreshDatabase;

    public function test_belongs_to_user(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrderModel::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($order->user->is($user));
    }
}
