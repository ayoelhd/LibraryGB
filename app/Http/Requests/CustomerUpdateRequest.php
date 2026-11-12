<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CustomerUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|string|max:255',
            'phone_number' => ['sometimes', 'string', 'max:10', Rule::unique('customers', 'phone_number')->ignore($this->route('customer'))],
            'address'      => 'sometimes|string|max:255',
            'password'     => 'sometimes|min:8|string',
            'balance'      => 'sometimes|integer',
        ];
    }
}
