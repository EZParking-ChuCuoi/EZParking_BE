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
        // Check if the parking lot exists
        $parkingLot = ParkingLot::find($id);
        if (!$parkingLot) {
            return response()->json(['error' => 'Parking lot not found'], 404);
        }

        // Retrieve the parking lot information
        $data = [
            'id' => $parkingLot->id,
            'nameParkingLot' => $parkingLot->nameParkingLot,
            'address' => $parkingLot->address,
            'address_latitude' => $parkingLot->address_latitude,
            'address_longitude' => $parkingLot->address_longitude,
            'openTime' => $parkingLot->openTime,
            'endTime' => $parkingLot->endTime,
            'desc' => $parkingLot->desc,
            'images' => json_decode($parkingLot->images)

        ];

        return response()->json([$data], 200);
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
        $data = DB::table("parking_lots")
            ->leftJoin('blocks', 'parking_lots.id', '=', 'blocks.parkingLotId')
            ->select(
                "parking_lots.*",
                DB::raw("6371 * acos(cos(radians(" . $lat . "))
            * cos(radians(parking_lots.address_latitude))
            * cos(radians(parking_lots.address_longitude) - radians(" . $lon . "))
            + sin(radians(" . $lat . "))
            * sin(radians(parking_lots.address_latitude))) AS distance")
            )
            ->having('distance', '<', 1.5)
            ->groupBy('parking_lots.id')
            ->get();
        foreach ($data as $parking_lot) {
            $parking_lot->images = json_decode($parking_lot->images);
        }
        return $data;
    }

    /**
     * @OA\Post(
     *     path="/api/parking-lot/create",
     *     tags={"Parking Lot"},
     *     summary="Create a new parking lot",
     *     description="Create a new parking lot with the specified details",
     *     operationId="createParkingLot",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\MediaType(
     *             mediaType="multipart/form-data",
     *             @OA\Schema(
     *                 @OA\Property(
     *                     property="userId",
     *                     type="integer",
     *                     example=1000000
     *                 ),
     *                 @OA\Property(
     *                     property="images[]",
     *                     type="array",
     *                     @OA\Items(
     *                         type="file"
     *                     ),
     *                     description="Array of images"
     *                 ),
     *                 @OA\Property(
     *                     property="openTime",
     *                     type="string",
     *                     format="time",
     *                     example="20:08"
     *                 ),
     *                 @OA\Property(
     *                     property="endTime",
     *                     type="string",
     *                     format="time",
     *                     example="21:08"
     *                 ),
     *                 @OA\Property(
     *                     property="nameParkingLot",
     *                     type="string",
     *                     example="Parking Lot Cong"
     *                 ),
     *                 @OA\Property(
     *                     property="address_latitude",
     *                     type="string",
     *                     example="16.060832"
     *                 ),
     *                 @OA\Property(
     *                     property="address_longitude",
     *                     type="string",
     *                     example="108.241491"
     *                 ),
     *                 @OA\Property(
     *                     property="address",
     *                     type="string",
     *                     example="101B Le Huu Tra"
     *                 ),
     *                 @OA\Property(
     *                     property="desc",
     *                     type="string",
     *                     example="gia ra dat an ninh coa"
     *                 )
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response="201",
     *         description="Parking lot created successfully",
     *     ),
     *     @OA\Response(
     *         response="422",
     *         description="Validation error",
     *     ),
     *     security={ {"passport":{}}}
     * )
     */

    public function createParkingLot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'userId' => 'required|integer',
            'images' => 'required|array|min:1',
            'images.*' => 'required|image',
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
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $parkingLot = new ParkingLot([
            'openTime' => $data['openTime'],
            'endTime' => $data['endTime'],
            'nameParkingLot' => $data['nameParkingLot'],
            'address_latitude' => $data['address_latitude'],
            'address_longitude' => $data['address_longitude'],
            'address' => $data['address'],
            'desc' => $data['desc'],
        ]);

        $imageLinks = [];

        foreach ($request->file('images') as $image) {
            $linkImage = CloudinaryStorage::upload($image->getRealPath(), $image->getClientOriginalName(), 'parkingLot/images');
            $imageLinks[] = $linkImage;
        }

        $parkingLot->images = json_encode($imageLinks);

        $parkingLot->save();

        $userParkingLot = new UserParkingLot([
            'userId' => $data['userId'],
            'parkingId' => $parkingLot->id,
        ]);

        $userParkingLot->save();

        return response()->json([
            'message' => 'Parking lot created successfully.',
            'data' => $parkingLot
        ], 201);
    }

    public function updateParkingLot(Request $request, $idParkingLot)
    {
        $validator = Validator::make($request->all(), [
            'images' => 'required|array|min:1',
            'images.*' => 'required|image',
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
            return response()->json([
                'error' => $validator->errors()
            ], 422);
        }

        $data = $validator->validated();

        $parkingLot = ParkingLot::findOrFail()->get();
        return 'cong';
    }
}
