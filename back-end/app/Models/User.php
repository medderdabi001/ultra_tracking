<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    protected $fillable = [
        'account_id',
        'name',
        'email',
        'password',
        'role',
        'status'
    ];

    protected $hidden = ['password', 'remember_token'];

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    // علاقة Assistant بالسيارات المسموح ليه يشوفها
    public function permittedCars()
    {
        return $this->belongsToMany(Car::class, 'user_car_permissions')
            ->withPivot('can_view', 'can_view_history', 'can_cut_engine')
            ->withTimestamps();
    }

    public function commands()
    {
        return $this->hasMany(DeviceCommand::class, 'requested_by');
    }
}
