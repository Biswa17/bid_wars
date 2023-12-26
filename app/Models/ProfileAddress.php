<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProfileAddress extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'address', 'state', 'city', 'pin'];

    public function profile()
    {
        return $this->belongsTo(Profile::class);
    }

}