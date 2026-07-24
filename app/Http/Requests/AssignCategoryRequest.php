<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;

class AssignCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->can('update', Campaign::class);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'category_slug' => 'required|string|exists:campaign_categories,slug',
            'order' => 'sometimes|integer|min:0',
            'featured' => 'sometimes|boolean',
            'featured_order' => 'sometimes|nullable|integer|min:1',
        ];
    }
}
