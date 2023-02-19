<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ParkingLot;
use App\Models\ParkingSlot;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;

class BlockParkingCarController extends Controller
{
    public function getBlock(Request $request,$id){
        $this->validate($request, [
            'carType'           => 'required',
        ]);
        $carType = $request->carType;
       $block = ParkingLot::join('blocks','blocks.parkingLotId', '=', 'parking_lots.id')
    //    ->join('parking_lots','parking_lots.id', '=', 'blocks.parkingLotId')
    //    ->groupBy('parking_slots.blockId')
       ->where('parking_lots.id','=',$id)
    //    ->havingRaw('parking_slots.carType','=',$carType)
       ->get(['blocks.nameBlock','blocks.id']);
       return  $block ? : null;
    }
    public function getSlot(Request $request,$id){
        $this->validate($request, [
            'carType'           => 'required',
        ]);
        $carType = $request->carType;
       $block = Block::find($id)->parkingSlots->select('id','slotCode','status',"carType",'desc','price')->get();
  
       return  $block ? : null;
    }
    

     

}
