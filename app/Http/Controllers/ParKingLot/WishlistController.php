<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\ParkingLot;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WishlistController extends Controller
{
   /**
     * @OA\Get(
     ** path="/api/user/{userId}/wishlist", tags={"Wishlist"}, 
     *  summary="detail parking lot with id", operationId="getWishlist",
     *   @OA\Parameter(
     *         name="userId",
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
    public function getWishlist($userId)
    {

        try {
            // Get user by ID
            $user = User::findOrFail($userId);

            // Get user's wishlist with parking lot details
            $parkingLotId = $user->wishlists->pluck('parkingLotId');

            $wishlist = ParkingLot::whereIn('id',$parkingLotId)->get();
         

            // Check if wishlist exists
            if (!$wishlist) {
                return response()->json([
                    'message' => 'Wishlist not found.'
                ], 404);
            }
            $data = [
                'id' => $wishlist->id,
                'nameParkingLot' => $wishlist->nameParkingLot,
                'address' => $wishlist->address,
                'address_latitude' => $wishlist->address_latitude,
                'address_longitude' => $wishlist->address_longitude,
                'openTime' => $wishlist->openTime,
                'endTime' => $wishlist->endTime,
                'desc' => $wishlist->desc,
                'images' => json_decode($wishlist->images)
    
            ];
            // Return the wishlist data
            return response()->json($data, 200);
        } catch (\Throwable $th) {
        }
    }



    //  /**
    //  * @OA\Post(
    //  ** path="/api/parking-lot/block/slots/create", tags={"Slot"}, 
    //  *  summary="create slot ", operationId="createSlot",
    //  *      @OA\Parameter(name="slotName",in="query",required=true,example="E3", @OA\Schema( type="string" )),
    //  *      @OA\Parameter(name="blockId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
    //  * 
    //  *@OA\Response( response=403, description="Forbidden"),
    //  * security={ {"passport":{}}}
    //  *)
    //  **/
    public function addWishList(Request $request)
    {
        $validatedData = $request->validate([
            'userId' => 'required|integer',
            'parkingLotId' => 'required|integer',
        ]);

        $userId = $validatedData['userId'];
        $parkingLotId = $validatedData['parkingLotId'];

        // Check if the user has already added the parking lot to their wishlist
        $existingWishlist = Wishlist::where('userId', $userId)
            ->where('parkingLotId', $parkingLotId)
            ->first();

        if ($existingWishlist) {
            return response()->json([
                'message' => 'This parking lot is already in the user\'s wishlist'
            ], 422);
        }

        $wishlist = Wishlist::create([
            'userId' => $userId,
            'parkingLotId' => $parkingLotId
        ]);

        return response()->json([
            'message' => 'Wishlist created successfully',
            'data' => $wishlist
        ]);
    }
}
