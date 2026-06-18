<?php

declare(strict_types=1);

namespace Tests\Unit\Policies;

use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use App\Policies\TravelOrderPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

final class TravelOrderPolicyTest extends TestCase
{
    use RefreshDatabase;

    private TravelOrderPolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();

        $this->policy = new TravelOrderPolicy;
    }

    public function test_admin_can_cancel_travel_order(): void
    {
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->create();

        $this->assertTrue($this->policy->cancel($admin, $order));
    }

    public function test_regular_user_cannot_cancel_travel_order(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrderModel::factory()->create();

        $this->assertFalse($this->policy->cancel($user, $order));
    }

    public function test_admin_can_approve_travel_order(): void
    {
        $admin = UserModel::factory()->admin()->create();
        $order = TravelOrderModel::factory()->create();

        $this->assertTrue($this->policy->approve($admin, $order));
    }

    public function test_regular_user_cannot_approve_travel_order(): void
    {
        $user = UserModel::factory()->create();
        $order = TravelOrderModel::factory()->create();

        $this->assertFalse($this->policy->approve($user, $order));
    }
}
