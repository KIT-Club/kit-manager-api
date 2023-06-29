<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      @OA\Property(property="id",type="integer"),
 *      @OA\Property(property="name",type="string"),
 *      @OA\Property(property="users", type="array", @OA\Items(ref="#/components/schemas/User")),
 * )
 */
class Role extends Model
{
    use HasFactory;

    protected $table = 'role';

    protected $fillable = [
        'name',
        'permissions',
        'is_admin',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'permissions' => 'array',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_role');
    }
}
