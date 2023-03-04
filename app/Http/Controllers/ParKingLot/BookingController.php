<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingSlot;
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
                $total_price=0;
                $total_price= $durationHours * $price;
                // Get difference in hours
                switch (true) {
                    case ($durationHours < 24):
                        $total_price -=  $durationHours * $price*5/100;
                        break;
                    case ($durationHours >= 24 && $durationHours < 24 * 7):
                        $total_price -=  $durationHours * $price*10/100;
                        break;
                    case ($durationHours >= 24 * 7 && $durationHours < 24 * 30):
                        $total_price -=  $durationHours * $price*20/100;
                        break;

                    case ($durationHours >= 24 * 30 && $durationHours < 24 * 365):
                        $total_price -=  $durationHours * $price*30/100;
                        break;

                    case ($durationHours >= 24 * 365):
                        $total_price -=  $durationHours * $price*40/100;
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
            $total =0;
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
                $total_price = $prices[$number]*$durationHours;
                $discount=0;
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
            $output["total"] =$total;
            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully',
                'data' => $output,
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'One or more slots are already booked during the requested time period',
            'data' => $bookedSlots
        ]);
    }
    /**
     * @OA\Get(
     ** path="/api/booking/show", tags={"Booking"}, 
     *  summary="Scan QRcode to get detail booking", operationId="getDetailQRcode",
     *  @OA\Parameter(name="userId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     *  @OA\Parameter(name="startDateTime",in="query",required=true,example="2023-02-27 14:30:00", @OA\Schema( type="string" )),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    
    public function getDetailQRcode(Request $request)
    {

        $validator = validator::make($request->all(), [
            'userId' => 'required|integer',
            'startDateTime' => 'required|date_format:Y-m-d H:i:s',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $userId = $dateData['userId'];
        $startDateTime = $dateData['startDateTime'];
        $outPut = Booking::join('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->join('blocks', 'blocks.id', '=', 'parking_slots.blockId')
            ->where('bookings.userId', $userId)
            ->where('bookings.bookDate', $startDateTime)
            ->get();
        return response()->json([
            'message' => 'Detail booking',
            'data' => $outPut,
        ], 200);
    }

    public function historyBooking($userId)
    {
        $bookings = Booking::select(
            'bookings.id as booking_id',
            'bookings.bookDate',
            'parking_slots.slotName',
            'blocks.nameBlock',
            'blocks.price',
            'blocks.carType',
            'parking_lots.nameParkingLot as parking_lot_name'
        )
            ->leftJoin('parking_slots', 'bookings.slotId', '=', 'parking_slots.id')
            ->leftJoin('blocks', 'parking_slots.blockId', '=', 'blocks.id')
            ->leftJoin('parking_lots', 'blocks.parkingLotId', '=', 'parking_lots.id')
            ->where('bookings.userId', '=', $userId)
            ->orderBy('bookings.bookDate', 'desc')
            ->get()
            ->groupBy('bookDate');

        $response = [];
        foreach ($bookings as $date => $bookingsByDate) {
            $totalPayment = $bookingsByDate->sum('payment');
            $parkingLotName = $bookingsByDate->isNotEmpty() ? $bookingsByDate->first()->parking_lot_name : null;
            $response[] = [
                'date' => $date,
                'total_payment' => $totalPayment,
                'parking_lot_name' => $parkingLotName,
                'bookings' => $bookingsByDate->toArray(),
            ];
        }

        return response()->json([
            'message' => 'Booking history',
            'data' => $response,
        ], 200);
    }
}
