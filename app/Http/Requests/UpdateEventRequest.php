<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEventRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    // public function authorize()
    // {
    //     return false;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|max:255',
            'start_date' => 'required|date',
            'end_date' => 'date|after_or_equal:start_date|nullable',
            'user_ids' => 'array',
            'user_ids.*' => 'required|distinct|integer|min:1',
        ];
    }
}
