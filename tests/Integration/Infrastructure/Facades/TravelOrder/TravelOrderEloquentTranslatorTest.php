<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\Facades\TravelOrder;

use App\Domain\TravelOrder\Entities\TravelOrder;
use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use App\Domain\TravelOrder\ValueObjects\TravelPeriod;
use App\Infrastructure\Facades\TravelOrder\TravelOrderEloquentTranslator;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class TravelOrderEloquentTranslatorTest extends TestCase
{
    public function test_round_trip_domain_to_array_and_back(): void
    {
        $translator = new TravelOrderEloquentTranslator;
        $order = TravelOrder::reconstitute(
            id: '550e8400-e29b-41d4-a716-446655440000',
            userId: 1,
            requesterName: 'Alice',
            destination: 'Lisboa',
            period: TravelPeriod::fromStrings('2026-11-01', '2026-11-10'),
            status: TravelOrderStatus::Solicitado,
        );

        $record = $translator->toPersistenceArray($order);
        $restored = $translator->toDomain($record);

        $this->assertSame($order->id(), $restored->id());
        $this->assertSame('Lisboa', $restored->destination());
        $this->assertSame(TravelOrderStatus::Solicitado, $restored->status());
    }

    #[DataProvider('invalidStringFieldProvider')]
    public function test_to_domain_throws_when_string_field_is_invalid(string $field, mixed $value): void
    {
        $translator = new TravelOrderEloquentTranslator;
        $record = $this->validRecord();
        $record[$field] = $value;

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage("Expected string for {$field}.");

        $translator->toDomain($record);
    }

    /**
     * @return array<string, array{0: string, 1: mixed}>
     */
    public static function invalidStringFieldProvider(): array
    {
        return [
            'missing id' => ['id', null],
            'numeric id' => ['id', 123],
            'missing status' => ['status', null],
        ];
    }

    public function test_to_domain_throws_when_user_id_is_not_int(): void
    {
        $translator = new TravelOrderEloquentTranslator;
        $record = $this->validRecord();
        $record['user_id'] = '1';

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Expected int for user_id.');

        $translator->toDomain($record);
    }

    /**
     * @return array<string, mixed>
     */
    private function validRecord(): array
    {
        return [
            'id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 1,
            'requester_name' => 'Alice',
            'destination' => 'Lisboa',
            'departure_date' => '2026-11-01',
            'return_date' => '2026-11-10',
            'status' => 'solicitado',
        ];
    }
}
