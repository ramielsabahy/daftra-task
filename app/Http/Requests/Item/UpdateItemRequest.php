<?php

namespace App\Http\Requests\Item;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateItemRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'sku'                 => ['sometimes', 'string', 'max:100', Rule::unique('items', 'sku')->ignore($this->route('item'))],
            'name'                => ['sometimes', 'string', 'max:255'],
            'description'         => ['nullable', 'string'],
            'unit'                => ['sometimes', 'string', 'max:50'],
            'low_stock_threshold' => ['sometimes', 'integer', 'min:0'],
        ];
    }
}
