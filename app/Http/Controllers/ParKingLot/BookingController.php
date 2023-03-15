<?php

namespace App\Http\Controllers\ParKingLot;

use App\Events\NotificationBooking;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use App\Notifications\BookingNotification;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/booking/slots", tags={"Booking"}, 
     *  summary="show detail booking", operationId="getSlotsByIdWithBlockName",
     *     @OA\Parameter(
     *         name="ids",
     *         in="query",
     *         example="[100000000,100000001]",
     *         description="An array of integers.",
     *         required=true,
     *         @OA\Schema(
     *             type="array",
     *             @OA\Items(
     *                 type="integer",
     *              
     *              )
     *         )
     *     ),
     *  @OA\Parameter(name="start_datetime",in="query",required=true,example="2023-03-01 14:30:00", @OA\Schema( type="string" )),
     *  @OA\Parameter(name="end_datetime",in="query",required=true,example="2023-04-01 14:30:00", @OA\Schema( type="string" )),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getSlotsByIdWithBlockName(Request $request)
    {
        // get all slots with the specified IDs
        // Convert $slotIds to an array if it's a string
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'start_datetime' => 'required|date_format:Y-m-d H:i:s',
            'end_datetime' => 'required|date_format:Y-m-d H:i:s|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $ids = $dateData['ids'];
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $slots = ParkingSlot::whereIn('id', $ids)->with('block')->get();
        // create an array to store the output slots
        $output = [];
        $total = 0;
        // loop through the input slot IDs
        foreach ($ids as $slotId) {
            // find the slot object with the current ID in the $slots array
            $slot = $slots->firstWhere('id', $slotId);
            // if a matching slot object was found, add it to the output array
            if ($slot) {
                $block = $slot->block;
                $price = $block->price;
                $startDatetime = Carbon::parse($dateData['start_datetime']);
                $endDatetime = Carbon::parse($dateData['end_datetime']);
                $durationHours = $endDatetime->diffInHours($startDatetime);
                $total_price = 0;
                $total_price = $durationHours * $price;
                // Get difference in hours
                switch (true) {
                    case ($durationHours < 24):
                        $total_price -=  $durationHours * $price * 5 / 100;
                        break;
                    case ($durationHours >= 24 && $durationHours < 24 * 7):
                        $total_price -=  $durationHours * $price * 10 / 100;
                        break;
                    case ($durationHours >= 24 * 7 && $durationHours < 24 * 30):
                        $total_price -=  $durationHours * $price * 20 / 100;
                        break;

                    case ($durationHours >= 24 * 30 && $durationHours < 24 * 365):
                        $total_price -=  $durationHours * $price * 30 / 100;
                        break;

                    case ($durationHours >= 24 * 365):
                        $total_price -=  $durationHours * $price * 40 / 100;
                        break;
                }

                $output['slots'][] = [
                    'slotId' => $slot->id,
                    'blockName' => $block->nameBlock,
                    'blockDesc' => $block->desc,
                    'carType' => $block->carType,
                    'price' => $price,
                    'durationHours' => $durationHours,
                    'total_price' => $total_price,
                ];
                $total += $total_price;
            }
        }
        $output['total'] = $total;
        $output['date'] = [
            "start_datetime" => $dateData['start_datetime'],
            "end_datetime" => $dateData['end_datetime'],

        ];
        return $output;
    }
    /**
     * @OA\POST(
     ** path="/api/booking", tags={"Booking"}, summary="booking now",
     * operationId="bookParkingLot",
     *     @OA\Parameter(
     *         name="slot_ids[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *  @OA\Parameter(name="user_id",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *      @OA\Parameter(name="start_datetime",in="query",required=true,example="2023-01-27 14:50:00", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="end_datetime",in="query",required=true,example="2023-02-01 14:50:00", @OA\Schema( type="string" )),
     *    @OA\Parameter(
     *         name="licensePlate[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="string"))
     *     ),
     * @OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function bookParkingLot(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'slot_ids' => 'required|array',
            'user_id' =>   'required',
            'licensePlate' => 'required|array',
            'start_datetime' => 'required|date',
            'end_datetime' => 'required|date|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $slotIds = $dateData['slot_ids'];
        $userIds =ParkingSlot::find($slotIds[0])->block->parkingLot->userParkingLot->pluck('userId')[0];
        $userId = $dateData['user_id'];
        $licensePlate = $dateData['licensePlate'];
        $startDatetime = $dateData['start_datetime'];
        $endDatetime = $dateData['end_datetime'];


        $bookedSlots = Booking::where(function ($query) use ($startDatetime, $endDatetime) {
            $query->where('bookDate', '<', $endDatetime)
                ->where('returnDate', '>', $startDatetime);
        })
            ->whereIn('slotId', $slotIds)
            ->pluck('slotId')
            ->toArray();
        $emptySlots = array_diff($slotIds, $bookedSlots);
        // If all requested slots are empty, create a new booking
        if (count($emptySlots) === count($slotIds)) {
            $number = 0;
            $output = [];
            $total = 0;
            $prices = ParkingSlot::whereIn('parking_slots.id', $emptySlots)
                ->select('blocks.price')
                ->join('blocks', 'blocks.id', '=', 'parking_slots.blockId')
                ->get()
                ->pluck('price')
                ->toArray();

            $startDatetime = Carbon::parse($dateData['start_datetime']);
            $endDatetime = Carbon::parse($dateData['end_datetime']);
            $durationHours = $endDatetime->diffInHours($startDatetime);
            // Get difference in hours

            foreach ($emptySlots as $slot) {
                $total_price = $prices[$number] * $durationHours;
                $discount = 0;
                switch (true) {
                    case ($durationHours < 24):
                        $discount = 5;
                        break;
                    case ($durationHours >= 24 && $durationHours < 24 * 7):
                        $discount = 10;
                        break;
                    case ($durationHours >= 24 * 7 && $durationHours < 24 * 30):
                        $discount = 20;
                        break;
                    case ($durationHours >= 24 * 30 && $durationHours < 24 * 365):
                        $discount = 30;
                        break;
                    case ($durationHours >= 24 * 365):
                        $discount = 40;
                        break;
                    default:
                        $discount = 0;
                        break;
                }

                $total_price -= $durationHours * $prices[$number] * $discount / 100;

                $booking = new Booking();
                $booking->licensePlate = $licensePlate[$number];
                $booking->userId = $userId;
                $booking->slotId = $slot;
                $booking->payment = $total_price;
                $booking->bookDate = $startDatetime;
                $booking->returnDate = $endDatetime;
                $booking->save();
                $number += 1;
                $output['booking'][] = $booking;
                $total += $total_price;
            }
            $output["total"] = $total;
            $output["idBookings"] = $slotIds;
            $output["idSpaceOwner"] = $userIds;
            event(new NotificationBooking([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $output,
            ]));
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $output,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'One or more slots are already booked during the requested time period',
            'data' => $bookedSlots,

        ]);
    }

    /**
     * @OA\Get(
     ** path="/api/booking/{userId}/history", tags={"History"}, summary="get history booking of user",
     * operationId="historyBookingSummary",
     *   @OA\Parameter(
     *         name="userId",
     *         in="path",
     *         required=true,
     *         description="id user booking",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    function historyBookingSummary($userId)
    {
        $bookings = Booking::select(
            'bookings.id',
            'bookings.bookDate',
            'bookings.returnDate',
            'bookings.payment',
            'parking_lots.nameParkingLot as parking_lot_name',
            'parking_lots.address',
            'parking_lots.id as idParkingLot',
            'user_parking_lots.userId',
            'bookings.created_at',
        )
            ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->join('user_parking_lots', 'user_parking_lots.parkingId', '=', 'parking_lots.id')
            ->where('bookings.userId', '=', $userId)
            ->orderBy('bookings.created_at', 'desc')
            ->get()
            ->groupBy('bookDate')->take(10);

        $response = [];
       
        foreach ($bookings as $date => $bookingsByDate) {
            $totalPayment = $bookingsByDate->sum('payment');
            $parkingLotName = $bookingsByDate->isNotEmpty() ? $bookingsByDate->first()->parking_lot_name : null;
            $bookingIds = $bookingsByDate->pluck('id');
            $bookDate = $bookingsByDate[0]['bookDate'];
            $returnDate = $bookingsByDate[0]['returnDate'];
            $address = $bookingsByDate[0]['address'];
            $idSpaceOwner = $bookingsByDate[0]['userId'];
            $created_at = $bookingsByDate[0]['created_at'];
            $response[] = [
                'bookDate' => $bookDate,
                'returnDate' => $returnDate,
                'address' => $address,
                'total_payment' => $totalPayment,
                'parking_lot_name' => $parkingLotName,
                'booking_count' => $bookingsByDate->count(),
                'booking_ids' => $bookingIds,
                'idSpaceOwner' => $idSpaceOwner?:null,
                'created_at' => $created_at?:null,
            ];
        }

        return response()->json([
            'message' => 'Booking history summary',
            'data' => $response,
        ], 200);
    }
    /**
     * @OA\Get(
     ** path="/api/booking/history/details", tags={"History"}, summary="get history detail booking of user",
     * operationId="historyBookingDetail",
     *     @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function historyBookingDetail(Request $request)
    {
        $bookingIds = $request->input('bookingIds');
        if (!is_array($bookingIds)) {
            return response()->json([
                'message' => 'Invalid input: bookingIds must be an array',
            ], 400);
        }

        $bookings = Booking::select(
            'bookings.id as booking_id',
            'bookings.bookDate',
            'bookings.licensePlate',
            'bookings.payment',
            'parking_slots.slotName',
            'blocks.nameBlock',
            'blocks.carType',
            'parking_lots.nameParkingLot as parking_lot_name'
        )
            ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->whereIn('bookings.id', $bookingIds)
            ->orderBy('bookings.bookDate', 'desc')
            ->get();

        $totalPayment = 0;
        foreach ($bookings as $booking) {
            $totalPayment += $booking->payment;
        }

        return response()->json([
            'message' => 'Booking history summary',
            'data' => [

                'bookings' => $bookings,
            ],
        ], 200);
    }
    /**
     * @OA\Get(
     ** path="/api/booking/show", tags={"QrCode"}, 
     *  summary="Scan QRcode to get detail booking", operationId="getDetailQRcode",
     *   @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/

    public function getDetailQRcode(Request $request)
    {

        $validator = validator::make($request->all(), [
            'bookingIds' => 'required|array',
            'bookingIds.*' => 'required|integer',

        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        $bookingIds = $request->input('bookingIds');

        $bookings= Booking::whereIn('id', $bookingIds)->get();
        $idSlot= $bookings->pluck('slotId')[0];
        $inForParking= ParkingSlot::find($idSlot)->block->parkingLot;

        $sumPayment= $bookings->sum('payment');
        $outPut['booking']=$bookings;
        $outPut['totalPrice']=$sumPayment;
        $inForUser=$inForParking->user;
     
  
        $outPut['inForSpaceOwner']=[
            'id'=>$inForUser->id,
            'phone'=>$inForUser->phone,
            'fullName'=>$inForUser->fullName,

        ];
        $outPut['inForParkingLot']=[
            'nameParkingLot'=>$inForParking->nameParkingLot,
            'address'=>$inForParking->address,
        ];
        return response()->json([
            'message' => 'Detail booking',
            'data' => $outPut,
        ], 200);
    }

    /**
     * @OA\Patch(
     ** path="/api/booking/update", tags={"QrCode"}, summary="Qr Code to confirm complete finish booking",
     * operationId="completeBooking",
     *     @OA\Parameter(
     *         name="bookingIds[]",
     *         in="query",
     *         required=true,
     *         description="Array of booking IDs",
     *         @OA\Schema(type="array", @OA\Items(type="integer"))
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function completeBooking(Request $request)
    {
        $validator = validator::make($request->all(), [
            'bookingIds' => 'required|array',
            'bookingIds.*' => 'required|integer',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        $dateData = $validator->validated();
        $bookingIds = $dateData['bookingIds'];
        $now = Carbon::now()->toDateTimeString();
        foreach ($bookingIds as &$value) {
            $booking = Booking::findOrFail($value);
            $booking->returnDate = $now;
            $booking->save();
        }
        return response()->json([
            'message' => 'Update success!',
        ], 200);
    }

   
}
