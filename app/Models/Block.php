<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Block extends Model
{
    use HasFactory;
    protected $table = 'blocks';
    protected $fillable = ['parkingLotId','blockCode','nameBlock','carType','price','desc','capacity'];
    public function parkingLot()
    {
        return $this->belongsTo(\App\Models\ParkingLot::class);
    }
    public function parkingSlots()
    {
        return $this->hasMany(\App\Models\ParkingSlot::class,'blockId');
    }
}
