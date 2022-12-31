<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      @OA\Property(property="id",type="integer"),
 *      @OA\Property(property="name",type="string"),
 * )
 */
class Committee extends Model
{
    use HasFactory;

    protected $table = 'committee';

    protected $fillable = [
        'name',
    ];
}
