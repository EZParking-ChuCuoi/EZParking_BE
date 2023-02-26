<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\Booking;
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
            ->select('parking_slots.*', 'bookings.bookDate', 'bookings.returnDate', DB::raw('CASE WHEN bookings.id IS NULL THEN "available" ELSE "blocked" END AS status'))
            ->where('parking_slots.blockId', $id)
            ->get();

        return response()->json([
            'data' => $slots,
        ]);
    }
    public function getSlotStatusByBookingDateTime2(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'start_datetime' => 'required|date_format:Y-m-d H:i:s',
            'end_datetime' => 'required|date_format:Y-m-d H:i:s|after:start_datetime',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $startDate=$dateData["start_datetime"];
        $endDate=$dateData["end_datetime"];
        // Lấy ra tất cả các block trong parking lot với $parkingLotId được chỉ định.
        $blocks = Block::where('parkingLotId', $id)->get();

        // Khởi tạo mảng để lưu trữ trạng thái của từng slot trong các block.
        $status = array();

        // Duyệt qua mỗi block.
        foreach ($blocks as $block) {

            // Lấy ra tất cả các slot trong block đó.
            $slots = ParkingSlot::where('blockId', $block->id)->get();

            // Khởi tạo mảng để lưu trữ trạng thái của từng slot trong block đó.
            $blockStatus = array();

            // Duyệt qua mỗi slot.
            foreach ($slots as $slot) {

                // Lấy ra tất cả các booking trong slot đó, với điều kiện thời gian bắt đầu và kết thúc của booking
                // phải nằm trong khoảng thời gian được chỉ định.
                $bookings = Booking::where('slotId', $slot->id)
                    ->where('bookDate', '<=', $endDate)
                    ->where('returnDate', '>=', $startDate)
                    ->get();

                // Nếu số lượng booking lớn hơn 0, có nghĩa là slot đã được đặt trong khoảng thời gian được chỉ định.
                // Ngược lại, slot sẵn sàng để đặt trong khoảng thời gian đó.
                if (count($bookings) > 0) {
                    $blockStatus[] = array(
                        'idSlot' => $slot->id,
                        'slotCode' => $slot->slotCode,
                        'status' => 'booked'
                    );
                } else {
                    $blockStatus[] = array(
                        'idSlot' => $slot->id,
                        'slotCode' => $slot->slotCode,
                        'status' => 'available'
                    );
                }
            }

            // Lưu trạng thái của từng slot trong block đó vào mảng chung.
            $status[] = array(
                'block_id' => $block->id,
                'carType' => $block->carType,
                'price' => $block->price,
                'desc' => $block->desc,
                'status' => $blockStatus
            );
        }

        return 
        response()->json([
            'data' => $status,
        ]);
    }
}
