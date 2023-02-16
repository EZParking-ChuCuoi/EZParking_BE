<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Models\Block;
use App\Models\ParkingLot;
use Dflydev\DotAccessData\Data;
use Illuminate\Http\Request;

class BlockParkingCarController extends Controller
{
    public function showBlockCategory($id){
        $data = Block::Where('parkingLotId',$id)->groupBy('carType')->get();
        return $data;
    }

    public function showBlockDetail(Request $request, $id){
        $validatedData = $request->validate([
            'carType' => 'required',
        ]);
        $category=$request->category;

        $data = Block::where('carType',$category)->parkingSlots;
        return $data;
    }

}
