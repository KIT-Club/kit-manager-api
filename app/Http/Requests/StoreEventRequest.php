<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'name' => 'required',
            'start_date' => 'required|date',
            'end_date' => 'date|after_or_equal:start_date',
            'user_ids' => 'required|array',
            'user_ids.*' => 'required|distinct|integer|min:1',
        ];
    }
}
