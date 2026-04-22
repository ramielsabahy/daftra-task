<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;

class StoreItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sku'                 => ['required', 'string', 'max:100', 'unique:items,sku'],
            'name'                => ['required', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'unit'                => ['sometimes', 'string', 'max:50'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
