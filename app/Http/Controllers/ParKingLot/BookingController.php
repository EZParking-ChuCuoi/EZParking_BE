<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    public function getSlotsByIdWithBlockName(Request $request) {


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
        $total=0;
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
        $output['total']=$total;
        $output['date']=[
            "start_datetime"=>$dateData['start_datetime'],
            "end_datetime"=>$dateData['end_datetime'],

        ];
        return $output;
    }
}
