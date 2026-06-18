<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelOrder;

use App\Domain\TravelOrder\ValueObjects\TravelOrderStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida filtros opcionais e paginação para listagem de pedidos de viagem.
 */
final class ListTravelOrdersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'page' => $this->input('page', 1),
            'per_page' => $this->input('per_page', config()->integer('travel-orders.pagination.default_per_page')),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $maxPerPage = config()->integer('travel-orders.pagination.max_per_page');

        return [
            'page' => ['required', 'integer', 'min:1'],
            'per_page' => ['required', 'integer', 'min:1', 'max:'.$maxPerPage],
            'status' => ['nullable', Rule::enum(TravelOrderStatus::class)],
            'destination' => ['nullable', 'string', 'max:255'],
            'created_from' => ['nullable', 'date'],
            'created_to' => ['nullable', 'date', 'after_or_equal:created_from'],
            'departure_from' => ['nullable', 'date'],
            'departure_to' => ['nullable', 'date', 'after_or_equal:departure_from'],
        ];
    }
}
