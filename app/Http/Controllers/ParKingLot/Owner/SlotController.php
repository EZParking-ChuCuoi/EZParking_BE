<?php

namespace App\Http\Controllers\ParKingLot\Owner;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ParkingSlot;
use App\Rules\UniqueSlotNameInBlock;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SlotController extends Controller
{
    /**
     * @OA\Get(
     ** path="/api/parking-lot/block/{blockId}/slots", tags={"Slot"}, summary="get all slot with id block",
     * operationId="getAllSlot",
     *   @OA\Parameter(
     *         name="blockId",
     *         in="path",
     *         required=true,
     *         description="ID of parking lot",
     *         example=1000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getAllSlot($blockId)
    {
        $block = Block::find($blockId);
        $slots = $block->slots;
        $startDateTime = Carbon::now();
        $endDateTime = Carbon::now();

        // Loop through each slot and determine if it is available or booked
        foreach ($slots as $slot) {
            $bookings = $slot->bookings()->where(function ($query) use ($startDateTime, $endDateTime) {
                $query->whereBetween('bookDate', [$startDateTime, $endDateTime])
                    ->orWhereBetween('returnDate', [$startDateTime, $endDateTime])
                    ->orWhere(function ($query) use ($startDateTime, $endDateTime) {
                        $query->where('bookDate', '<', $startDateTime)
                            ->where('returnDate', '>', $endDateTime);
                    });
            })->get();

            if ($bookings->count() > 0) {
                $slot->status = 'booked';
            } else {
                $slot->status = 'available';
            }
        }

        return response()->json($slots);
    }

    /**
     * @OA\Post(
     ** path="/api/parking-lot/block/slots/create", tags={"Slot"}, 
     *  summary="create slot ", operationId="createSlot",
     *      @OA\Parameter(name="slotName",in="query",required=true,example="E3", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="blockId",in="query",required=true,example=1000000, @OA\Schema( type="integer" )),
     * 
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function createSlot(Request $request)
    {

        $blockId = $request->input('blockId');
        $block = Block::findOrFail($blockId);

        $validator = Validator::make($request->all(), [
            'slotName' => [
                'required',
                'string',
                new UniqueSlotNameInBlock($block)
            ],
            'blockId' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $slot = new ParkingSlot();
        $slot->slotName = $request->slotName;
        $slot->blockId = $request->blockId;
        $slot->save();

        return response()->json([
            'message' => 'Slot created successfully.',
            'data' => $slot
        ], 201);
    }
    /**
     * @OA\Get(
     ** path="/api/parking-lot/block/slots/{slotId}", tags={"Slot"}, summary="get detail slot with id slot",
     * operationId="getDetailSlot",
     *   @OA\Parameter(
     *         name="slotId",
     *         in="path",
     *         required=true,
     *         description="ID of slot",
     *         example=100000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function getDetailSlot($slotId)
    {
        $slot = ParkingSlot::find($slotId);
        if (!$slot) {
            return response()->json(['error' => 'Slot not found'], 404);
        }
        return response()->json($slot);
    }
    /**
     * @OA\Put(
     ** path="/api/parking-lot/block/slots/update/{slotId}", tags={"Slot"}, 
     *  summary="update slot", operationId="updateSlot",
     *   @OA\Parameter(
     *         name="slotId",
     *         in="path",
     *         required=true,
     *         description="ID of slot",
     *         example=100000000,
     *         @OA\Schema(type="integer"),
     *     ),
     *      @OA\Parameter(name="slotName",in="query",required=true,example="E3", @OA\Schema( type="string" )),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function updateSlot(Request $request, $slotId)
    {
        $validator = Validator::make($request->all(), [
            'slotName' => 'required|string|unique:parking_slots',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }
        $slot = ParkingSlot::find($slotId);
        if (!$slot) {
            return response()->json(['error' => 'Slot not found'], 404);
        }


        $slot->slotName = $request->slotName;
        $slot->save();

        return response()->json($slot);
    }

    public function deleteSlots(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'ids' => 'required|array',
            'ids.*' => 'integer',
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        $ids = $request->input('ids');
        $deletedSlots = [];

        foreach ($ids as $id) {
            $slot = ParkingSlot::find($id);
            if (!$slot) {
                return response()->json(['error' => 'Slot not found for ID: ' . $id], 404);
            }
            $deletedSlots[] = $slot->id;
            $slot->delete();
        }

        if (count($deletedSlots) == 0) {
            return response()->json(['error' => 'No slots were deleted'], 404);
        }

        return response()->json(['message' => 'Slots deleted successfully', 'deleted_slots' => $deletedSlots]);
    }
}
