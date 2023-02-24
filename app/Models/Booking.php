<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;
    protected $table = 'bookings';
    protected $fillable = ['userId','slotId','bookDate','returnDate','payment'];
    public function slots()
    {
        return $this->belongsTo(\App\Models\ParkingSlot::class,'slotId');
    }
    public function users()
    {
        return $this->hasMany(\App\Models\User::class);
    }
}
