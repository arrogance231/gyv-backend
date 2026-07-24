<?php

namespace App\Http\Requests;

use App\Models\Campaign;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCampaignRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'status' => ['required', 'string', Rule::in(['draft', 'published', 'scheduled', 'archived'])],
            'summary' => 'nullable|string',
            'description' => 'nullable|string',
            'image' => 'nullable|string',
            'date_label' => 'nullable|string|max:100',
            'date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'year' => 'nullable|string|max:10',
            'supporters_label' => 'nullable|string|max:100',
            'signatures_label' => 'nullable|string|max:100',
            'cta_text' => 'nullable|string|max:100',
            'link' => 'nullable|string|max:255',
            'signup_url' => 'nullable|string|max:255',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string',
        ];
    }
}
