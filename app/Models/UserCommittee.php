<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCommittee extends Model
{
    use HasFactory;

    protected $table = 'user_committee';

    protected $fillable = [
        'user_id',
        'committee_id',
    ];
}
