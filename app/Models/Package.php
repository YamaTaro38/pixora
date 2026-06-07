<?php
// app/Models/Package.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Models\PackageGallery;

class Package extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'name', 'slug', 'description', 'image', 'price', 'down_payment',
        'duration_hours', 'edited_photos', 'location_type',
        'inclusions', 'is_active', 'sort_order'
    ];
    
    protected $casts = [
        'inclusions' => 'array',
        'price' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'is_active' => 'boolean'
    ];
    
    public function galleries()
    {
        return $this->hasMany(PackageGallery::class)->orderBy('sort_order');
    }
    
    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        
        // Default images based on package name
        $defaultImages = [
            'wedding' => 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600',
            'prewedding' => 'https://images.unsplash.com/photo-1511285560929-80b456fea0bc?w=600',
            'family' => 'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=600',
        ];
        
        $lowerName = strtolower($this->name);
        foreach ($defaultImages as $key => $url) {
            if (str_contains($lowerName, $key)) {
                return $url;
            }
        }
        
        return 'https://images.unsplash.com/photo-1519741497674-611481863552?w=600';
    }
    
    public function getGalleryImagesAttribute()
    {
        return $this->galleries->pluck('image')->toArray();
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
    
    public function getFormattedPriceAttribute()
    {
        return 'Rp ' . number_format($this->price, 0, ',', '.');
    }
    
    public function getFormattedDownPaymentAttribute()
    {
        if ($this->down_payment) {
            return 'Rp ' . number_format($this->down_payment, 0, ',', '.');
        }
        return null;
    }
}