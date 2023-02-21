<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class BlockParkingCarController extends Controller
{
    public function getBlock($id)
    {
        $blockData = ParkingLot::find($id)->blocks()->orderBy('carType', 'asc')->get();
        return $blockData ?: null;
    }
    public function getSlotStatusByBookingDateTime(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_datetime' => 'required|date_format:Y-m-d H:i:s',
            'end_datetime' => 'required|date_format:Y-m-d H:i:s|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $startDatetime = $dateData["start_datetime"];
        $endDatetime = $dateData["end_datetime"];
        $slots = ParkingSlot::leftJoin('bookings', function ($join) use ($startDatetime, $endDatetime) {
            $join->on('parking_slots.id', '=', 'bookings.slotId')
                ->where(function ($query) use ($startDatetime, $endDatetime) {
                    $query->whereBetween('bookDate', [$startDatetime, $endDatetime])
                        ->orWhereBetween('returnDate', [$startDatetime, $endDatetime])
                        ->orWhere(function ($query) use ($startDatetime, $endDatetime) {
                            $query->where('bookDate', '<', $startDatetime)
                                ->where('returnDate', '>', $endDatetime);
                        });
                });
        })
            ->select('parking_slots.*','bookings.bookDate','bookings.returnDate', DB::raw('CASE WHEN bookings.id IS NULL THEN "available" ELSE "blocked" END AS status'))
            ->where('parking_slots.blockId', $id)
            ->get();

        return response()->json([
            'data' => $slots,
        ]);
    }
}
