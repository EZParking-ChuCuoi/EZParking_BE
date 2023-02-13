<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class UserParkingLot extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'user_parking_lots';
    protected $fillable = ['userId','parkingId'];
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
    public function parkingLot()
    {
        return $this->belongsTo(\App\Models\ParkingLot::class);
    }
}
