<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserCarPermission extends Model
{
    protected $fillable = [
        'user_id',
        'car_id',
        'can_view',
        'can_view_history',
        'can_cut_engine'
    ];

    protected $casts = [
        'can_view' => 'boolean',
        'can_view_history' => 'boolean',
        'can_cut_engine' => 'boolean',
    ];
}
