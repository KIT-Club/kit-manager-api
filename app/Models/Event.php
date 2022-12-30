<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @OA\Schema(
 *      @OA\Property(property="id",type="integer"),
 *      @OA\Property(property="name",type="string"),
 *      @OA\Property(property="description",type="string"),
 *      @OA\Property(property="start_date",type="string"),
 *      @OA\Property(property="end_date",type="string"),
 * )
 */
class Event extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'start_date',
        'end_date',
    ];

    protected $table = 'event';

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_event');
    }
}
