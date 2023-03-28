<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      @OA\Property(property="username",type="string"),
 *      @OA\Property(property="role_ids", type="array", @OA\Items(type="number")),
 *      @OA\Property(property="committee_ids", type="array", @OA\Items(type="number")),
 * )
 */
class StoreUserRequest extends FormRequest
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
            'username' => 'required|string|regex:/^[A-Za-z]{2}[0-9]{6}$/|unique:user,username,NULL,NULL,deleted_at,NULL',
            'role_ids' => 'required|array|max:1|min:1',
            'role_ids.*' => 'required|distinct|integer|min:1',
            'committee_ids' => 'required|array|min:1',
            'committee_ids.*' => 'required|distinct|integer|min:1',
        ];
    }
}
