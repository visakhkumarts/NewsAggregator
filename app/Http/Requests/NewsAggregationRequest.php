<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class NewsAggregationRequest extends FormRequest
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
            'sources' => 'nullable|array',
            'sources.*' => 'string|in:newsapi,guardian,nytimes',
            'categories' => 'nullable|array',
            'categories.*' => 'string',
            'limit' => 'nullable|integer|min:1|max:100',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'sources.*.in' => 'The selected source is not supported.',
            'limit.max' => 'The limit cannot exceed 100.',
        ];
    }
}






