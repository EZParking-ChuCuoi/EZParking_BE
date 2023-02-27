<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Clound\CloudinaryStorage;
use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Models\User;
use App\Models\UserParkingLot;
use App\Services\Interfaces\IParKingLotService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ParKingLotController extends Controller
{
    public function __construct(private readonly IParKingLotService $parKingLot)
    {
    }

    public function index()
    {
        return $this->parKingLot->getAllParkingLot(true);
    }

    public function getPriceOfParkingLot($id)
    {
        $price = ParkingLot::find($id)->blocks()->orderBy('price')->get('price');
        $priceData['priceFrom'] = $price[0]['price'];
        $priceData['priceTo'] = $price[sizeof($price) - 1]['price'];
        return $priceData ?: null;
    }
    public function getInfoParkingLot($id)
    {
        $parData = ParkingLot::where('id', $id)->get(['id','image', 'openTime', 'endTime', 'nameParkingLot', 'address', 'desc',])->toArray();
        return $parData;
    }


    public function showCommentOfParking($id)
    {
        $data = ParkingLot::join('comments', 'parking_lots.id', '=', 'comments.parkingId')
            ->join('users', 'users.id', '=', 'comments.userId')->where('parking_lots.id', $id)
            ->orderBy('created_at', 'DESC')
            ->get(['comments.*', 'users.fullName', 'users.avatar']);
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
        $data = DB::table("parking_lots")->select(
            "parking_lots.*",
            DB::raw("6371 * acos(cos(radians(" . $lat . ")) 
    
            * cos(radians(parking_lots.address_latitude)) 
    
            * cos(radians(parking_lots.address_longitude) - radians(" . $lon . ")) 
    
            + sin(radians(" . $lat . ")) 
    
            * sin(radians(parking_lots.address_latitude))) AS distance")
        )->having('distance', '<', 1.5)

            ->get();
        return $data;
    }

    public function createParkingLot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required',
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
            'openTime' => [
            'required',
            'date_format:H:i',
            'before:endTime',
        ],
        'endTime' => [
            'required',
            'date_format:H:i',
            'after:openTime',
        ],
            'nameParkingLot' => 'required|string|max:255',
            'address_latitude' => 'required',
            'address_longitude' => 'required',
            'address' => 'required|string|max:255',
            'desc' => 'required',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $parkingLot = new ParkingLot();
        $parkingLot->openTime = $dateData['openTime'];
        $parkingLot->endTime = $dateData['endTime'];
        $parkingLot->nameParkingLot = $dateData['nameParkingLot'];
        $parkingLot->address_latitude = $dateData['address_latitude'];
        $parkingLot->address_longitude = $dateData['address_longitude'];
        $parkingLot->address = $dateData['address'];
        $parkingLot->desc = $dateData['desc'];
        $image= $request->file('image');
        if ($request->hasFile('image')) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(),'parkingLot/images'); 
            $parkingLot->image = $linkImage;
        }
        $parkingLot->save();
        $user_parkingLot = new UserParkingLot([
            'userId' => $dateData['userId'],
            'parkingId' => $parkingLot->id,
        ]);
        $user_parkingLot->save();
        return response()->json([
            'message' => 'Parking lot created successfully.',
            'data' => $parkingLot
        ], 201);
    }

   
} 
