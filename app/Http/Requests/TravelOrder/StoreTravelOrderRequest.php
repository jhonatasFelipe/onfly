<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelOrder;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Valida dados para criação de pedido de viagem.
 */
final class StoreTravelOrderRequest extends FormRequest
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
            'destination' => ['required', 'string', 'max:255'],
            'departure_date' => ['required', 'date', 'after_or_equal:today'],
            'return_date' => ['required', 'date', 'after_or_equal:departure_date'],
        ];
    }
}
