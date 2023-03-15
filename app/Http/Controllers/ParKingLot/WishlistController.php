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
     *  summary="get all parkingLot have wishlist", operationId="getWishlist",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="id user",
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
            $wishlistData = [];

            foreach ($wishlist as $parkingLot) {
                $wishlistData[] = [
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
            }
            // Return the wishlist data
            return response()->json($wishlistData, 200);
        } catch (\Throwable $th) {
        }
    }



   /**
     * @OA\Post(
     ** path="/api/user/wishlist/add", tags={"Wishlist"}, 
     *  summary="add wishlist or Delete wishlist", operationId="addWishList",
         *  @OA\Parameter(name="userId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
         *  @OA\Parameter(name="parkingLotId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),

     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
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
            $existingWishlist->delete();
            return response()->json([
                'message' => 'Delete wishlist success!'
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
