<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @OA\Schema(
 *      @OA\Property(property="id",type="integer"),
 *      @OA\Property(property="username",type="string"),
 *      @OA\Property(property="name",type="string"),
 *      @OA\Property(property="email",type="string"),
 *      @OA\Property(property="birthday",type="string"),
 *      @OA\Property(property="avatar",type="string"),
 *      @OA\Property(property="class",type="string"),
 *      @OA\Property(property="major",type="string"),
 *      @OA\Property(property="facebook",type="string"),
 *      @OA\Property(property="github",type="string"),
 * )
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $table = 'user';

    protected $fillable = [
        'username',
        'name',
        'email',
        'birthday',
        'avatar',
        'class',
        'major',
        'facebook',
        'github',
    ];

    protected $hidden = [];

    protected $casts = [];
}
