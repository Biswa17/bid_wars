<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'name', 'category_id'];

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('order_number');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
