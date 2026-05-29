<?php
// app/Models/PackageGallery.php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PackageGallery extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'package_id', 'image', 'caption', 'sort_order'
    ];
    
    public function package()
    {
        return $this->belongsTo(Package::class);
    }
    
    public function getImageUrlAttribute()
    {
        if ($this->image && file_exists(public_path('storage/' . $this->image))) {
            return asset('storage/' . $this->image);
        }
        return $this->image;
    }
}