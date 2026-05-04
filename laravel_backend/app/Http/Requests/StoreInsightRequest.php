<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreInsightRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'account_id' => 'required', 'exist:accounts,id',
            'since_date' => 'required', 'date',
            'until_date' => 'required', 'date', 'after_or_equal:since_date',

            'data' => 'required | array',
            'data.impressions' => 'nullable', 'integer', 'min:0',
            'data.reach' => 'nullable', 'integer', 'min:0',
            'data.profile_views' => 'nullable', 'integer', 'min:0'
        ];
    }
}
