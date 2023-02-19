<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ParkingLot;
use App\Models\User;
use App\Services\Interfaces\IParKingLotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ParKingLotController extends Controller
{
    public function __construct( private readonly IParKingLotService $parKingLot ) 
    {
    }

    public function index()
    {
        return $this->parKingLot->getAllParkingLot(true);
    }

    public function getPriceOfParkingLot($id){
        $data =Block::where('parkingLotId',$id)->orderBy('price')->get('price');
        return ['priceFrom'=>$data[0]['price'],'priceTo'=>$data[sizeof($data)-1]['price']];
    }
    public function getInfoParkingLot($id){
        $parData = ParkingLot::where('id',$id)->get(['image','openTime','endTime','nameParkingLot','address','desc',])->toArray();
        return $parData;
    }


    public function showCommentOfParking($id)
    {
        $data = ParkingLot::join('comments', 'parking_lots.id', '=', 'comments.parkingId')
              ->join('users', 'users.id', '=', 'comments.userId')->where('parking_lots.id',$id)
              ->get(['comments.*', 'users.fullName','users.avatar']);
        return $data;
    }
     public function showParkingLotNearLocation(Request $request)
    {
        $validatedData = $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);
        $lat = $request->latitude;
        $lon = $request->longitude;
        $data =DB::table("parking_lots")->select("parking_lots.*"

            ,DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
    
            * cos(radians(parking_lots.address_latitude)) 
    
            * cos(radians(parking_lots.address_longitude) - radians(" . $lon . ")) 
    
            + sin(radians(" .$lat. ")) 
    
            * sin(radians(parking_lots.address_latitude))) AS distance"))->having('distance','<',1.5)

            ->get()->toArray();
        return $data;
    }
}
