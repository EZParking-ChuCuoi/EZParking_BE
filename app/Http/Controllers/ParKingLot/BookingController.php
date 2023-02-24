<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\ParkingSlot;

class BookingController extends Controller
{
    public function getSlotsByIdWithBlockName($ids) {
        // get all slots with the specified IDs
        // Convert $slotIds to an array if it's a string
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $slots = ParkingSlot::whereIn('id', $ids)->with('block')->get();
        // create an array to store the output slots
        $output = [];
        // loop through the input slot IDs
        foreach ($ids as $slotId) {
            // find the slot object with the current ID in the $slots array
            $slot = $slots->firstWhere('id', $slotId);
            // if a matching slot object was found, add it to the output array
            if ($slot) {
                $output[] = [
                    'slotId' => $slot->id,
                    'blockName' => $slot->block->nameBlock,
                    'carType' => $slot->block->carType,
                    'price' => $slot->block->price,
                ];
            }
        }
        return $output;
    }
}
