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
    
        // Set the start and end datetimes to the current date and time
        $currentTime = now();
        $nextDayTime = now();
    
        // Loop through the user parking lots and add the associated parking lots to the array
        foreach ($userParkingLots as $userParkingLot) {
            $parkingLot = [
                'idParking' => $userParkingLot->parkingLot->id,
                'nameParkingLot' => $userParkingLot->parkingLot->nameParkingLot,
                'available' => 0,
                'booked' => 0,
                'totalRevenue' => 0,
                'numberOfBlocks' => $userParkingLot->parkingLot->blocks->count()
            ];
    
            // Get the slots for the parking lot
            $slots = $userParkingLot->parkingLot->blocks->flatMap(function ($block) {
                return $block->slots;
            });
    
            // Loop through the slots and check their availability for the current time period
            foreach ($slots as $slot) {
                $bookings = $slot->bookings ()->where(function ($query) use ($currentTime, $nextDayTime) {
                    $query->where(function ($query) use ($currentTime, $nextDayTime) {
                        $query->where('bookDate', '>=', $currentTime)
                              ->where('bookDate', '<', $nextDayTime);
                    })->orWhere(function ($query) use ($currentTime, $nextDayTime) {
                        $query->where('returnDate', '>', $currentTime)
                              ->where('returnDate', '<=', $nextDayTime);
                    })->orWhere(function ($query) use ($currentTime, $nextDayTime) {
                        $query->where('bookDate', '<', $currentTime)
                              ->where('returnDate', '>', $nextDayTime);
                    });
                })->get();
            
                if ($bookings->isEmpty()) {
                    $parkingLot['available']++;
                } else {
                    $parkingLot['booked']++;
                    foreach ($bookings as $booking) {
                        $parkingLot['totalRevenue'] += $booking->payment;
                    }
                }
            }
    
            $parkingLots[] = $parkingLot;
        }
    
        // Return a response with the parking lots and their availability counts
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
     *         example="day",
     *         @OA\Schema(type="string"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getRevenueDetails($parkingLotId, $period)
    {
        $now = Carbon::now(); // Get the current time

        // Determine the time period to query
        if ($period == 'day') { // Check if the period is for a day
            // Get sales statistics for the current day
            $start = $now->startOfDay(); // Get the start time of the current day
            $end = $now->endOfDay(); // Get the end time of the current day
            $groupBy = DB::raw('DATE(bookDate)'); // Group the sales by date
            $format = 'Y-m-d'; // Set the date format
        } elseif ($period == 'month') { // Check if the period is for a month
            // Get sales statistics for the current month
            $start = $now->startOfMonth(); // Get the start time of the current month
            $end = $now->endOfMonth(); // Get the end time of the current month
            $groupBy = DB::raw('DATE_FORMAT(bookDate, "%Y-%m")'); // Group the sales by year and month
            $format = 'Y-m'; // Set the date format
        } elseif ($period == 'year') { // Check if the period is for a year
            // Get sales statistics for the current year
            $start = $now->startOfYear(); // Get the start time of the current year
            $end = $now->endOfYear(); // Get the end time of the current year
            $groupBy = DB::raw('YEAR(bookDate)'); // Group the sales by year
            $format = 'Y'; // Set the date format
        } else { // If an invalid period is specified
            // Invalid period specified
            return null; // Return null
        }

        // Get the sales data from the database
        $sales = DB::table('bookings')
            ->select(DB::raw("{$groupBy} as period"), DB::raw('SUM(bookings.payment) as total_sales'), DB::raw('COUNT(DISTINCT bookings.userId) as total_users'))
            ->join('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->join('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->join('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->join('users', 'bookings.userId', '=', 'users.id')
            ->where('parking_lots.id', $parkingLotId)
            ->groupBy(DB::raw($groupBy))
            ->get(); // Retrieve the sales data

        // Fill in any missing periods with zero sales
        $start = Carbon::parse($sales->first()->period)->startOf($period); // Get the start time of the first period
        $end = Carbon::parse($sales->last()->period)->endOf($period); // Get the end time of the last period
        $periods = $this->getPeriods($start, $end, $format); // Get an array of all periods within the specified time frame
        $sales = $this->fillMissingPeriods($periods, $sales, $groupBy->getValue()); // Add any missing periods with zero sales
        $periodArray = $sales->pluck('period')->toArray();
        $totalSalesArray = $sales->pluck('total_sales')->toArray();
        $totalUsersArray = $sales->pluck('total_users')->toArray();
        return response()->json([
            'message' => "Success!",
            'data' => [
                'periodArray' => $periodArray,
                'totalSalesArray' => $totalSalesArray,
                'totalUsersArray' => $totalUsersArray,
            ]
        ], 200); // Return a JSON response with the sales data
    }


    private function getPeriods($start, $end, $format)
    {
        $periods = [];
        $interval = CarbonInterval::day(); // Set interval to one day

        $period = Carbon::parse($start); // Parse the start date into Carbon object
        while ($period <= $end) { // Loop through each day until the end date is reached
            $periods[] = $period->format($format); // Format the period according to the specified format and add it to the array
            $period = $period->addDays(1)->startOf('day'); // Move to the next day and start from the beginning of the day
        }
        return $periods; // Return the array of periods
    }

    private function fillMissingPeriods($periods, $sales, $format)
    {
        $missingPeriods = array_diff($periods, $sales->pluck('period')->toArray()); // Find the missing periods between the specified period array and the sales period array

        foreach ($missingPeriods as $missingPeriod) { // Loop through each missing period
            $sales->push((object) ['period' => $missingPeriod, 'total_sales' => 0]); // Push an object with the missing period and zero sales into the sales array
        }

        return $sales->sortBy('period'); // Sort the sales array by period and return it
    }
}
