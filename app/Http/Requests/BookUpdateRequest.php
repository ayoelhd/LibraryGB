<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BookUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'         => 'sometimes|required|string|max:255',
            'ISBN'         => ['sometimes', 'required', 'string', 'max:255', Rule::unique('books', 'ISBN')->ignore($this->route('book'))],
            'author'       => 'sometimes|required|string|max:255',
            'publisher'    => 'sometimes|required|string|max:255',
            'publish_date' => 'sometimes|required|date',
            'category_id'  => 'sometimes|required|exists:categories,id',
        ];
    }
}
