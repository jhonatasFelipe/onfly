<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelOrder;

use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida filtros opcionais para listagem de pedidos de viagem.
 */
final class ListTravelOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            // Filtra por status do pedido (solicitado, aprovado ou cancelado).
            'status' => ['nullable', Rule::enum(TravelOrderStatus::class)],
            // Filtra por destino (busca parcial).
            'destination' => ['nullable', 'string', 'max:255'],
            // Data inicial de criação do pedido.
            'created_from' => ['nullable', 'date'],
            // Data final de criação do pedido.
            'created_to' => ['nullable', 'date'],
            // Data inicial de partida.
            'departure_from' => ['nullable', 'date'],
            // Data final de partida.
            'departure_to' => ['nullable', 'date'],
        ];
    }
}
