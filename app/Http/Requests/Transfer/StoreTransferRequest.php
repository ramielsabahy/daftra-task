<?php

namespace App\Http\Requests\Transfer;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransferRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'from_warehouse_id' => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->whereNull('deleted_at')
            ],
            'to_warehouse_id'   => [
                'required',
                'integer',
                Rule::exists('warehouses', 'id')->whereNull('deleted_at'),
                'different:from_warehouse_id'
            ],
            'item_id'           => ['required', 'integer', 'exists:items,id'],
            'quantity'          => ['required', 'integer', 'min:1'],
            'notes'             => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'to_warehouse_id.different' => 'Source and destination warehouse must be different.',
        ];
    }
}
