<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Car extends Model
{
    protected $fillable = [
        'account_id', 'marque', 'model', 'matricule', 
        'color', 'fuel_type', 'mileage', 'status'
    ];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // التاريخ كامل ديال الأجهزة اللي تركبوا
    public function deviceAssignments()
    {
        return $this->hasMany(CarDeviceAssignment::class);
    }

    // الجهاز اللي مركب دابا (النشط)
    public function activeDeviceAssignment()
    {
        return $this->hasOne(CarDeviceAssignment::class)->whereNull('removed_at');
    }

    public function permittedUsers()
    {
        return $this->belongsToMany(User::class, 'user_car_permissions');
    }

    public function commands()
    {
        return $this->hasMany(DeviceCommand::class);
    }
}
