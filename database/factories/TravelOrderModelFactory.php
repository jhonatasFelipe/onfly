<?php

declare(strict_types=1);

namespace Database\Factories;

use App\Domain\Shared\ValueObjects\Uuid;
use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use App\Infrastructure\Persistence\Eloquent\UserModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<TravelOrderModel> */
class TravelOrderModelFactory extends Factory
{
    protected $model = TravelOrderModel::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departure = fake()->dateTimeBetween('+1 day', '+30 days');
        $return = (clone $departure)->modify('+'.fake()->numberBetween(1, 14).' days');

        return [
            'id' => Uuid::generate()->value(),
            'user_id' => UserModel::factory(),
            'requester_name' => fake()->name(),
            'destination' => fake()->city(),
            'departure_date' => $departure->format('Y-m-d'),
            'return_date' => $return->format('Y-m-d'),
            'status' => 'solicitado',
        ];
    }

    public function approved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'aprovado',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'cancelado',
        ]);
    }
}
