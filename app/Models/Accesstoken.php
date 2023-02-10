<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Accesstoken extends Model
{
    use HasFactory;
    protected $fillable = [
        'roleID',
        'userID',
        'token',
        'username',
        'expires_at',
    ];
}
