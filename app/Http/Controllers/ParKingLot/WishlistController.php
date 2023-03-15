<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
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
