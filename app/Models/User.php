<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class User extends Model
{
    
    use HasFactory, SoftDeletes;
    protected $table = 'users';
    protected $fillable = ['email','full_name', 'avatar','password', 'status'];
    public function roleDFUser()
    {
        return $this->hasMany(\App\Models\RoleDFUser::class,"roleId");
    }
    public function booking()
    {
        return $this->hasMany(\App\Models\Booking::class,'userId');
    }
    public function userParkingLot()
    {
        return $this->hasMany(\App\Models\UserParkingLot::class);
    }
    public function comments()
    {
        return $this->hasMany(\App\Models\Comment::class,'userId');
    }




}
