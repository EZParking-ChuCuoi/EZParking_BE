<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\User;
use App\Models\Wishlist;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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
            $parkingLotIds = $user->wishlists->pluck('parkingLotId')->toArray();
            // echo $parkingLotId;
            $wishlist = ParkingLot::whereIn('id', $parkingLotIds)->get();
            if (!$wishlist) {
                return response()->json([
                    'message' => 'Wishlist not found.'
                ], 404);
            }
            $bookingsByDate = Booking::select(
                'parking_lots.id as parking_lot_id',
                'parking_lots.nameParkingLot',
                'parking_lots.address',
            )
                ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
                ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
                ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
                ->whereIn('parking_lots.id', $parkingLotIds)
                ->where('bookings.userId', $userId)
                ->groupBy('parking_lots.id', 'bookings.bookDate')
                ->get();
                
                $bookingsCountByParkingLot = $bookingsByDate->groupBy('parking_lot_id')
                ->map(function ($item) {
                    $output = [
                        'parking_lot_id' => $item[0]->parking_lot_id,
                        'nameParkingLot' => $item[0]->nameParkingLot,
                        'address' => $item[0]->address,
                        'count' => count($item) ?? 0
                    ];
                    return $output;
                })
                ->values()
                ->toArray();
                
            
             
            // Return the wishlist data
            return response()->json($bookingsCountByParkingLot, 200);
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
            ], 200);
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
