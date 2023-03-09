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

    /**
     * @OA\Get(
     ** path="/api/parking-lot/{id}/slots", tags={"Block"}, 
     *  summary="get all slot in this block with detail status", operationId="getSlotStatusByBookingDateTime2",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Parameter(name="start_datetime",in="query",required=true,example="2023-03-01 14:30:00", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="end_datetime",in="query",required=true,example="2023-04-01 14:30:00", @OA\Schema( type="string" )),
     * 
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
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
        $startDate = $dateData["start_datetime"];
        $endDate = $dateData["end_datetime"];
        // Lấy ra tất cả các block trong parking lot với $parkingLotId được chỉ định.
        $blocks = Block::where('parkingLotId', $id)->get();

        // Khởi tạo mảng để lưu trữ trạng thái của từng slot trong các block.
        $status = array();

        // Duyệt qua mỗi block.
        foreach ($blocks as $block) {

            // Lấy ra tất cả các slot trong block đó.
            $slots = $block->slots;

            // Kiểm tra nếu block không có slot thì bỏ qua.
            if (count($slots) === 0) {
                continue;
            }
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
                        'slotName' => $slot->slotName,
                        'status' => 0
                    );
                } else {
                    $blockStatus[] = array(
                        'idSlot' => $slot->id,
                        'slotName' => $slot->slotName,
                        'status' => 1
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
