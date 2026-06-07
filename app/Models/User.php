<?php
// app/Models/User.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'avatar',
        'role',
        'google_id',
        'google_token',
        'google_refresh_token',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'google_token',
        'google_refresh_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isCustomer()
    {
        return $this->role === 'customer';
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}