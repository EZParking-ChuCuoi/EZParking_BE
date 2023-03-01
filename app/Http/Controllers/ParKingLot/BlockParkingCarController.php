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
     ** path="/api/parking-lot/{id}/blocks", tags={"Block"}, 
     *  summary="get all slot in this block", operationId="getBlock",
     *   @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *          example=1000000,
     *         description="ID of the parking lot to retrieve",
     *         @OA\Schema(type="integer")
     *     ),
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
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
    /**
     * @OA\Post(
     ** path="/api/parking-lot/block/create", tags={"Block"}, 
     *  summary="create block ,slot", operationId="createBlockSlot",
     *      @OA\Parameter(name="parkingLotId",in="query",required=true,example="1000000", @OA\Schema( type="integer" )),
     *      @OA\Parameter(name="nameBlock",in="query",required=true,example="Khu a", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="carType",in="query",required=true,example="4-16SLOT", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="desc",in="query",required=true,example="an toan cao", @OA\Schema( type="string" )),
     *      @OA\Parameter(name="price",in="query",required=true,example=14000, @OA\Schema( type="integer" )),
     *      @OA\Parameter(name="numberOfSlot",in="query",required=true,example=50, @OA\Schema( type="integer" )),
     * 
     *@OA\Response( response=403, description="Forbidden"),
     * security={ {"passport":{}}}
     *)
     **/
    public function createBlockSlot(Request $request)
    {

        $validator = Validator::make($request->all(), [
            "parkingLotId" => 'required',
            "nameBlock" => 'required|string|max:255',
            "carType" => 'required|in:4-16SLOT,16-34SLOT',
            "desc" => 'required|string',
            "price" => 'required|digits_between:1,99999999999999',
            "numberOfSlot" => 'required|integer|min:1|max:100',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();
        $block = new Block();
        $block->parkingLotId = $dateData["parkingLotId"];
        $block->nameBlock = $dateData["nameBlock"];
        $block->desc = $dateData["desc"];
        $block->carType = $dateData["carType"];
        $block->price = $dateData["price"];
        $block->save();

        $numberOfSlot = $dateData["numberOfSlot"];
        $blockNameLastChar = strtoupper(substr($block->nameBlock, -1));

        for ($i = 1; $i <= $numberOfSlot; $i++) {
            $slot = new ParkingSlot();
            $slot->slotName = $blockNameLastChar . $i;
            $block->slots()->save($slot);
        }
        return response()->json([
            'message' => 'Block created successfully',
            'block' => $block,
        ], 201);
    }
}
