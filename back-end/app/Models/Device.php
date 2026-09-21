<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Device extends Model
{
    protected $fillable = [
        'imei', 'traccar_device_id', 'model', 
        'phone_number', 'protocol', 'status'
    ];

    public function assignments()
    {
        return $this->hasMany(CarDeviceAssignment::class);
    }

    // السيارة المرتبطة بهاد الجهاز دابا
    public function currentAssignment()
    {
        return $this->hasOne(CarDeviceAssignment::class)->whereNull('removed_at');
    }
}
