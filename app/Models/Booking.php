<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $fillable = ['userId','slotId','bookDate','returnDate','payment','bookingStatus','rating','comment'];
    public function parkingSlot()
    {
        return $this->belongsTo(\App\Models\ParkingSlot::class);
    }
    public function user()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
