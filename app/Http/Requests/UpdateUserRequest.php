<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * @OA\Schema(
 *      @OA\Property(property="email",type="string"),
 *      @OA\Property(property="facebook",type="string"),
 *      @OA\Property(property="github",type="string"),
 * )
 */
class UpdateUserRequest extends FormRequest
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
            'email' => 'nullable|email',
            'facebook' => 'nullable|url',
            'github' => 'nullable|url',

            'name' => 'exclude',
            'birthday' => 'exclude',
            'username' => 'exclude',
            'avatar' => 'exclude',
            'class' => 'exclude',
            'major' => 'exclude',
        ];
    }
}
