<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'first_name', 'last_name', 'phone_number', 'profile_pic'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function addresses()
    {
        return $this->hasMany(ProfileAddress::class);
    }
}
