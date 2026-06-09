<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Payment extends Model
{
    use HasFactory;

    protected $fillable = [
        'booking_id',
        'method',
        'transaction_id',
        'snap_token',
        'amount',
        'fee',
        'status',
        'va_number',
        'bank_code',
        'payment_code',
        'qr_url',
        'qr_string',
        'deeplink',
        'raw_response',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'paid_at' => 'datetime',
        'expired_at' => 'datetime',
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'raw_response' => 'array',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
