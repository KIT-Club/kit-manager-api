<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      @OA\Property(property="name",type="string"),
 *      @OA\Property(property="description",type="string"),
 *      @OA\Property(property="start_date",type="string"),
 *      @OA\Property(property="end_date",type="string"),
 *      @OA\Property(property="user_ids", type="array", @OA\Items(type="number")),
 * )
 */
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
            'name' => 'required|max:255',
            'start_date' => 'required|date',
            'end_date' => 'date|after_or_equal:start_date',
            'user_ids' => 'array',
            'user_ids.*' => 'required|distinct|integer|min:1',
        ];
    }
}
