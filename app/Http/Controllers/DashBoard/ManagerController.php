<?php

namespace App\Http\Controllers\DashBoard;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonInterval;
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
                "idParkingLot" => $parkingLotId,
                "numberUserBooking" => $numUsers,
                "numberBlock" => $numBlocks,
                "revenue" => $revenue,
            ]
        ], 200);
    }
    /**
     * @OA\Get(
     ** path="/api/dashboard/{parkingLotId}/revenue/{period}", tags={"DashBoard"}, summary="Statistics parking lot block, revenue,userBooking by number",
     * operationId="getRevenueDetails",
     *   @OA\Parameter(
     *         name="parkingLotId",
     *         in="path",
     *         required=true,
     *         description="id Parking lot",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *     @OA\Parameter(
     *         name="period",
     *         in="path",
     *         required=true,
     *         description="period day or week or month or year",
     *         example=day,
     *         @OA\Schema(type="string"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getRevenueDetails($parkingLotId, $period)
    {
        $now = Carbon::now(); // Get the current time

        if ($period == 'day') {
            // Get sales statistics for the current day
            $start = $now->startOfDay();
            $end = $now->endOfDay();
            $groupBy = DB::raw('DATE(bookDate)');
            $format = 'Y-m-d';
        }  elseif ($period == 'month') {
            // Get sales statistics for the current month
            $start = $now->startOfMonth();
            $end = $now->endOfMonth();
            $groupBy = DB::raw('DATE_FORMAT(bookDate, "%Y-%m")');
            $format = 'Y-m';
        } elseif ($period == 'year') {
            // Get sales statistics for the current year
            $start = $now->startOfYear();
            $end = $now->endOfYear();
            $groupBy = DB::raw('YEAR(bookDate)');
            $format = 'Y';
        } else {
            // Invalid period specified
            return null;
        }

        $sales = DB::table('bookings')
            ->select(DB::raw("{$groupBy} as period"), DB::raw('SUM(bookings.payment) as total_sales'))
            ->join('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->join('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->join('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->join('users', 'bookings.userId', '=', 'users.id')
            ->where('parking_lots.id', $parkingLotId)
            ->groupBy(DB::raw($groupBy))
            // ->orderBy(DB::raw($groupBy))
            ->get();

        // Fill in any missing periods with zero sales
        $start = Carbon::parse($sales->first()->period)->startOf($period);
        $end = Carbon::parse($sales->last()->period)->endOf($period);
        $periods = $this->getPeriods($start, $end, $format);
        $sales = $this->fillMissingPeriods($periods, $sales, $groupBy->getValue());
        return response()->json([
            'message' => "Success!",
            'data' => $sales
        ], 200);
    }

    private function getPeriods($start, $end, $format)
    {
        $periods = [];
        $interval = CarbonInterval::day();

        $period = Carbon::parse($start);
        while ($period <= $end) {
            $periods[] = $period->format($format);
            $period = $period->addDays(1)->startOf('day');
        }

        return $periods;
    }

    private function fillMissingPeriods($periods, $sales, $format)
    {
        $missingPeriods = array_diff($periods, $sales->pluck('period')->toArray());

        foreach ($missingPeriods as $missingPeriod) {
            $sales->push((object) ['period' => $missingPeriod, 'total_sales' => 0]);
        }

        return $sales->sortBy('period');
    }
}
