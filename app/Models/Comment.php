<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
    use HasFactory;
    protected $table = 'comments';
    protected $fillable = ['nameParkingLot','address','image','openTime','endTime','desc','status'];
    public function userParkingLot()
    {
        return $this->belongsTo(\App\Models\ParkingLot::class);
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
