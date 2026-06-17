<?php

declare(strict_types=1);

namespace App\Http\Requests\TravelOrder;

use App\Infrastructure\Persistence\Eloquent\TravelOrderModel;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Valida e autoriza atualização de status de pedido de viagem.
 */
final class UpdateTravelOrderStatusRequest extends FormRequest
{
    /**
     * Delega autorização à policy conforme o status solicitado (aprovado/cancelado).
     */
    public function authorize(): bool
    {
        $status = $this->input('status');

        if (! in_array($status, ['cancelado', 'aprovado'], true)) {
            return true;
        }

        /** @var TravelOrderModel|null $order */
        $order = TravelOrderModel::query()->find($this->route('id'));

        if ($order === null) {
            return true;
        }

        return match ($status) {
            'cancelado' => $this->user()?->can('cancel', $order) ?? false,
            'aprovado' => $this->user()?->can('approve', $order) ?? false,
        };
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
