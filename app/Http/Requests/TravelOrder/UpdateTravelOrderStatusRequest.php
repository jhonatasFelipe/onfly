<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelOrder;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida atualização de status de pedido de viagem.
 */
final class UpdateTravelOrderStatusRequest extends FormRequest
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
            'status' => ['required', Rule::in(['aprovado', 'cancelado'])],
        ];
    }
}
