<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ParkingSlot extends Model
{
    use HasFactory;
    protected $table = 'parking_slots';
    protected $fillable = ['blockId','slotCode','price','status','desc'];
    public function block()
    {
        return $this->belongsTo(\App\Models\Block::class);
    }
    public function booking()
    {
        return $this->hasMany(\App\Models\Booking::class);
    }
}
