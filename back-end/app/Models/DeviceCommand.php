<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeviceCommand extends Model
{
    protected $fillable = [
        'car_id',
        'device_id',
        'requested_by',
        'type',
        'status',
        'traccar_command_id',
        'error_message',
        'requested_at',
        'sent_at',
        'completed_at'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'sent_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
}
