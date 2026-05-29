<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PoseReference extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'category', 'title', 'image_url', 'thumbnail_url',
        'description', 'source', 'sort_order', 'is_active'
    ];
    
    protected $casts = [
        'is_active' => 'boolean'
    ];
    
    public static function getCategories()
    {
        return [
            'wedding' => 'Wedding',
            'prewedding' => 'Prewedding',
            'family' => 'Keluarga',
            'portrait' => 'Portrait',
            'maternity' => 'Maternity',
            'graduation' => 'Graduation'
        ];
    }
}