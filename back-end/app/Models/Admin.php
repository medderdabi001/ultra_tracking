<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class Admin extends Authenticatable
{
    use HasFactory;

    protected $fillable = ['name', 'email', 'password'];

    protected $hidden = ['password', 'remember_token'];

    public function accounts()
    {
        return $this->hasMany(Account::class, 'created_by');
    }

    public function deviceAssignments()
    {
        return $this->hasMany(CarDeviceAssignment::class, 'assigned_by');
    }
}