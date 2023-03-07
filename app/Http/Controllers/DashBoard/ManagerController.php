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
                $bookings = $slot->bookings()->where(function ($query) use ($currentTime, $nextDayTime) {
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
     ** path="/api/dashboard/{userId}/revenue/{period}", tags={"DashBoard"}, summary="Statistics parking lot block, revenue,userBooking by number",
     * operationId="getRevenueDetails",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="id user manage parking lto lot",
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
    public function getRevenueDetails($userId, $period)
    {
        $now = Carbon::now();

        if ($period == 'day') {
            $start = $now->startOfDay();
            $end = $now->endOfDay();
            $groupBy = DB::raw('DATE(bookDate)');
            $format = 'Y-m-d';
        } elseif ($period == 'month') {
            $start = $now->startOfMonth();
            $end = $now->endOfMonth();
            $groupBy = DB::raw('DATE_FORMAT(bookDate, "%Y-%m")');
            $format = 'Y-m';
        } elseif ($period == 'year') {
            $start = $now->startOfYear();
            $end = $now->endOfYear();
            $groupBy = DB::raw('YEAR(bookDate)');
            $format = 'Y';
        } else {
            return response()->json([
                'message' => "Invalid period specified.",
                'data' => null
            ], 400);
        }

        $sales = DB::table('bookings')
            ->select(
                DB::raw("{$groupBy} as period"),
                DB::raw('SUM(bookings.payment) as total_sales'),
                DB::raw('COUNT(DISTINCT bookings.userId) as total_users'),
                DB::raw('COUNT(*) as total_bookings')
            )
            ->join('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->join('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->join('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->join('users', 'bookings.userId', '=', 'users.id')
            ->join('user_parking_lots', 'users.id', '=', 'user_parking_lots.userId')
            ->where('user_parking_lots.userId', $userId)
            ->groupBy($groupBy)
            ->get();
        // return $sales;

        if ($sales->isEmpty()) {
            return response()->json([
                'message' => "No sales data available for the specified period.",
                'data' => null
            ], 404);
        }

        $start = Carbon::parse($sales->first()->period)->startOf($period);
        $end = Carbon::parse($sales->last()->period)->endOf($period);
        $periods = $this->getPeriods($start, $end, $format);
        $sales = $this->fillMissingPeriods($periods, $sales, $groupBy->getValue());

        $periodLabels = $sales->pluck('period')->toArray();
        $salesTotals = $sales->pluck('total_sales')->toArray();
        $uniqueUsers = $sales->pluck('total_users')->toArray();
        foreach ($uniqueUsers as &$value) {
            if (is_null($value)) {
                $value = 0;
            }
        }
        $bookingCounts = $sales->pluck('total_bookings')->toArray();
        foreach ($bookingCounts as &$value) {
            if (is_null($value)) {
                $value = 0;
            }
        }
        return response()->json([
            'message' => "Success!",
            'data' => [
                'periodLabels' => $periodLabels,
                'salesTotals' => $salesTotals,
                'uniqueUsers' => $uniqueUsers,
                'bookingCounts' => $bookingCounts,
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
