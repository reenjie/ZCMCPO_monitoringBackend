<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'FK_PoID',
        'extendedCount',
        'duration_date',
        'emailed_date',
        'received_date',
        'delivered_date',
        'completed_date',
        'cancelled_date',
        'DueDate',
        'confirmation',
        'confirmedby',
        'status',
        'remarks'
    ];
}
