<?php
// app/Models/Booking.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Booking extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'booking_code',
        'public_token',
        'customer_name',
        'customer_phone',
        'customer_email',
        'package_id',
        'booking_date',
        'time_slot',
        'total_price',
        'down_payment',
        'payment_status',
        'session_status',
        'booking_status',
        'slot_locked_until',
        'special_requests',
        'admin_notes',
        'expires_at',
        'paid_at',
        'payment_method',
        'payment_transaction_id',
        'payment_details'
    ];
    
    protected $casts = [
        'booking_date' => 'date',
        'expires_at' => 'datetime',
        'slot_locked_until' => 'datetime',
        'paid_at' => 'datetime',
        'payment_details' => 'array'
    ];
    
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    
    public function getPublicUrlAttribute()
    {
        return url('/booking/' . $this->public_token);
    }
    
    public function getTimeSlotLabelAttribute()
    {
        $slots = [
            'morning' => 'Pagi (08:00-11:00)',
            'afternoon' => 'Siang (13:00-16:00)',
            'evening' => 'Sore (17:00-20:00)'
        ];
        return $slots[$this->time_slot] ?? $this->time_slot;
    }
    
    public function isAccessible()
    {
        if ($this->payment_status === 'expired') return false;
        if ($this->payment_status === 'cancelled') return false;
        if ($this->session_status === 'cancelled') return false;
        return true;
    }
}