<?php

declare(strict_types=1);

namespace Tests\Unit\Notifications;

use App\Notifications\TravelOrderCancelledNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Tests\TestCase;

final class TravelOrderCancelledNotificationTest extends TestCase
{
    public function test_via_returns_mail_and_database_channels(): void
    {
        $notification = new TravelOrderCancelledNotification([
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 1,
            'status' => 'cancelado',
        ]);

        $this->assertSame(['mail', 'database'], $notification->via(new \stdClass()));
    }

    public function test_to_mail_contains_order_id(): void
    {
        $notification = new TravelOrderCancelledNotification([
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 1,
            'status' => 'cancelado',
        ]);

        $mail = $notification->toMail(new \stdClass());

        $this->assertInstanceOf(MailMessage::class, $mail);
        $this->assertSame('Pedido de viagem cancelado', $mail->subject);
    }

    public function test_to_array_returns_payload(): void
    {
        $payload = [
            'order_id' => '550e8400-e29b-41d4-a716-446655440000',
            'user_id' => 1,
            'status' => 'cancelado',
        ];
        $notification = new TravelOrderCancelledNotification($payload);

        $this->assertSame($payload, $notification->toArray(new \stdClass()));
    }
}
