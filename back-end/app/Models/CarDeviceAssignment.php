<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarDeviceAssignment extends Model
{
    protected $fillable = [
        'car_id', 'device_id', 'assigned_by', 'installed_at', 'removed_at'
    ];

    protected $casts = [
        'installed_at' => 'datetime',
        'removed_at' => 'datetime',
    ];

    public function car()
    {
        return $this->belongsTo(Car::class);
    }

    public function device()
    {
        return $this->belongsTo(Device::class);
    }

    public function installer()
    {
        return $this->belongsTo(Admin::class, 'assigned_by');
    }
}
