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
class Committee extends Model
{
    use HasFactory;

    protected $table = 'committee';

    protected $fillable = [
        'name',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_committee');
    }
}
