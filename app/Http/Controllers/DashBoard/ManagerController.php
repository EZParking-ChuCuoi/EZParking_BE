<?php

namespace App\Http\Controllers\DashBoard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManagerController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/dashboard/parkingLots/{userId}", tags={"DashBoard"}, summary="get all parkinglot by user management",
     * operationId="getParkingUserManage",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="id user management",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getParkingUserManage($userId)
    {
        // Get the user with the specified ID
        $user = User::findOrFail($userId);

        // Get the user parking lots
        $userParkingLots = $user->userParkingLots;

        // Initialize an array to store the parking lots
        $parkingLots = [];

        // Loop through the user parking lots and add the associated parking lots to the array
        foreach ($userParkingLots as $userParkingLot) {
            $parkingLots[] = [
                'idParking' => $userParkingLot->parkingLot->id,
                'nameParkingLot' => $userParkingLot->parkingLot->nameParkingLot,
            ];
        }

        // Return a response with the parking lots
        return response()->json([
            'message' => 'Success!',
            'data' => $parkingLots
        ]);
    }
    /**
     * @OA\Get(
     ** path="/api/dashboard/{$parkingLotId}", tags={"DashBoard"}, summary="Statistics parking lot block, revenue,userBooking by number",
     * operationId="parkingLotBookingStats",
     *   @OA\Parameter(
     *         name="parkingLotId",
     *         in="path",
     *         required=true,
     *         description="id Parking lot",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function parkingLotBookingStats($parkingLotId)
    {
         // Get the count of users who have booked the parking lot
        $numUsers = DB::table('bookings')
        ->join('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
        ->join('blocks', 'parking_slots.blockId', '=', 'blocks.id')
        ->join('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
        ->where('parking_lots.id', $parkingLotId)
        ->distinct('bookings.userId')
        ->count('bookings.userId');
        // Retrieve the users who have booked the specified parking lot
        $numBlocks = DB::table('blocks')
        ->where('parkingLotId', $parkingLotId)
        ->count();

        $revenue = Booking::whereHas('slot.block.parkingLot', function ($query) use ($parkingLotId) {
            $query->where('id', $parkingLotId);
        })
            ->where('bookDate', '<=', Carbon::now())
            ->where('returnDate', '<=', Carbon::now())
            ->sum('payment');

        // Return a response that includes the users who have booked the parking lot and the parking lot manager
        return response()->json([
            'message' => "Success!",
            'data' => [
                "idParkingLot"=>$parkingLotId,
                "numberUserBooking"=>$numUsers,
                "numberBlock"=>$numBlocks,
                "revenue"=>$revenue,
            ]
        ],200);
    }

   
}
