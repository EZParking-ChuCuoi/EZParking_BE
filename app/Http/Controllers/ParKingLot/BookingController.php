<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
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
                $output['slots'][] = [
                    'slotId' => $slot->id,
                    'blockName' => $slot->block->nameBlock,
                    'blockDesc' => $slot->block->desc,
                    'carType' => $slot->block->carType,
                    'price' => $slot->block->price,
                ];
                $total += $slot->block->price;
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
            'price' => 'required',
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
        $price = $dateData['price'];
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
            $output = ['dfdfdd'];
            foreach ($emptySlots as $slot) {
                $booking = new Booking();
                $booking->licensePlate = $licensePlate[$number];
                $booking->userId = $userId;
                $booking->slotId = $slot;
                $booking->payment = $price;
                $booking->bookDate = $startDatetime;
                $booking->returnDate = $endDatetime;
                $booking->save();
                $number += 1;
                $output[] = $booking;
            }


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
}
