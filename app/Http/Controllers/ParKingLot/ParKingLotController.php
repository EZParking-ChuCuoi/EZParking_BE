<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\User;
use App\Services\Interfaces\IParKingLotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParKingLotController extends Controller
{
    public function __construct(
        private readonly IParKingLotService $parKingLot
    ) {
    }

    public function index()
    {
        return $this->parKingLot->getAllParkingLot(true);
    }
    public function showComentOfParking($id)
    {
        $id =1000000;
        
        $data = ParkingLot::join('comments', 'parking_lots.id', '=', 'comments.parkingId')
              ->join('users', 'users.id', '=', 'comments.userId')->where('parking_lots.id',$id)
              ->get(['comments.*', 'users.fullName','users.avatar','parking_lots.*']);
        return $data;

    }
     public function showParkingLotnearLocation()
    {

        $lat = 16.060575;
        $lon = 108.240783;
        $data =
        DB::table("parking_lots")

        ->select("parking_lots.*"
    
            ,DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
    
            * cos(radians(parking_lots.address_latitude)) 
    
            * cos(radians(parking_lots.address_longitude) - radians(" . $lon . ")) 
    
            + sin(radians(" .$lat. ")) 
    
            * sin(radians(parking_lots.address_latitude))) AS distance"))->having('distance','<',1)

            ->get()->toArray();
        return $data;
    }

}
