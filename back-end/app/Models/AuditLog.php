<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public $timestamps = false; // حيت عندنا غير created_at

    protected $fillable = [
        'account_id',
        'user_id',
        'admin_id',
        'action',
        'entity_type',
        'entity_id',
        'data'
    ];

    protected $casts = [
        'data' => 'array',
        'created_at' => 'datetime',
    ];
}
