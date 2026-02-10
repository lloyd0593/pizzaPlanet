<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AddToCartRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'pizza_id' => 'nullable|exists:pizzas,id',
            'size' => 'required|in:small,medium,large',
            'crust' => 'required|in:thin,regular,thick,stuffed',
            'quantity' => 'required|integer|min:1|max:20',
            'toppings' => 'nullable|array',
            'toppings.*' => 'exists:toppings,id',
            'is_custom' => 'nullable|boolean',
        ];
    }
}
