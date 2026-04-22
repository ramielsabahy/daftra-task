<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class IndexInventoryRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'warehouse_id' => ['sometimes', 'integer', 'exists:warehouses,id'],
            'item_id'      => ['sometimes', 'integer', 'exists:items,id'],
            'sku'          => ['sometimes', 'string', 'max:100'],
            'low_stock'    => ['sometimes', 'boolean'],
            'per_page'     => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
