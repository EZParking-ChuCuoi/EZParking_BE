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
    /**
     * @OA\Get(
     ** path="/api/parking-lot", tags={"Parking Lot"}, 
     *  summary="admin get all parking lot", operationId="index",
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/

    public function index()
    {
        return $this->parKingLot->getAllParkingLot(true);
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/info/price", tags={"Parking Lot"}, 
     *  summary="detail price of parking lot with id", operationId="getPriceOfParkingLot",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getPriceOfParkingLot($id)
    {
        $price = ParkingLot::find($id)->blocks()->orderBy('price')->get('price');
        $priceData['priceFrom'] = $price[0]['price'];
        $priceData['priceTo'] = $price[sizeof($price) - 1]['price'];
        return $priceData ?: null;
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/info", tags={"Parking Lot"}, 
     *  summary="detail parking lot with id", operationId="getInfoParkingLot",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getInfoParkingLot($id)
    {
        $parData = ParkingLot::where('id', $id)->get(['id', 'image', 'openTime', 'endTime', 'nameParkingLot', 'address', 'desc',])->toArray();
        return $parData;
    }

    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/info/comment", tags={"Parking Lot"}, 
     *  summary="detail comment of parking lot", operationId="showCommentOfParking",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function showCommentOfParking($id)
    {
        $data = ParkingLot::join('comments', 'parking_lots.id', '=', 'comments.parkingId')
            ->join('users', 'users.id', '=', 'comments.userId')->where('parking_lots.id', $id)
            ->orderBy('created_at', 'DESC')
            ->get(['comments.*', 'users.fullName', 'users.avatar']);
        return $data;
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot/location", tags={"Parking Lot"}, 
     *  summary="show location near user ", operationId="showParkingLotNearLocation",
     *  @OA\Parameter(name="latitude",in="query",required=true,example=16.060832, @OA\Schema( type="string" )),
     *  @OA\Parameter(name="longitude",in="query",required=true,example=108.24149, @OA\Schema( type="string" )),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
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
    /**
     * @OA\Post(
     ** path="/api/parking-lot/create", tags={"Parking Lot"}, 
     *  summary="create parking lot ", operationId="createParkingLot",
     *   @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="userId",
     *                     type="integer",
     *                      example=1000000,
     *                 ),
     *                  @OA\Property(
     *                     property="image",
     *                     type="file"
     *                 ),@OA\Property(
     *                     property="openTime",
     *                     type="date-time",
     *                      example="20:08"
     *                 ),@OA\Property(
     *                     property="endTime",
     *                     type="date-time",
     *                      example="21:08"
     *                 ),@OA\Property(
     *                     property="nameParkingLot",
     *                     type="string",
     *                      example="Parking Lot Cong"
     *                 ),@OA\Property(
     *                     property="address_latitude",
     *                     type="string",
     *                     example="16.060832"
     *                 ),@OA\Property(
     *                     property="address_longitude",
     *                     type="string",
     *                     example="108.241491"
     *                 ),@OA\Property(
     *                     property="address",
     *                     type="string",
     *                      example="101B Le Huu Tra"
     *                 ),@OA\Property(
     *                     property="desc",
     *                     type="string",
     *                      example="gia ra dat an ninh coa"
     *                 )
     *                 
     *             )
     *         )
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
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
        $image = $request->file('image');
        if ($request->hasFile('image')) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'parkingLot/images');
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
    public function uploadImage(Request $request){
        $validatedData = $request->validate([
            'image' => 'required',
        ]);
        $image = $request->image;
        if ($request->hasFile('image')) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'parkingLot/images');
            return response()->json([
                'message' => 'Parking lot created successfully.',
                'data' => $linkImage
            ], 201);
        }
        return 'not found';
    }
}
