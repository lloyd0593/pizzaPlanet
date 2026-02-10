<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PaymentRequest extends FormRequest
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
        $rules = [
            'payment_method' => 'required|in:credit_card,paypal',
        ];

        if ($this->input('payment_method') === 'credit_card') {
            $rules['card_number'] = 'required|string|size:16';
            $rules['card_name'] = 'required|string|max:255';
            $rules['card_expiry'] = 'required|string|regex:/^\d{2}\/\d{2}$/';
            $rules['card_cvv'] = 'required|string|size:3';
        }

        if ($this->input('payment_method') === 'paypal') {
            $rules['paypal_email'] = 'required|email|max:255';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'card_number.size' => 'Card number must be exactly 16 digits.',
            'card_cvv.size' => 'CVV must be exactly 3 digits.',
            'card_expiry.regex' => 'Expiry must be in MM/YY format.',
        ];
    }
}
