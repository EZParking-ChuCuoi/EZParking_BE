<?php

namespace App\Http\Controllers\ParKingLot;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\IParKingLotService;
use Illuminate\Http\Request;

class ParKingLotController extends Controller
{
    public function __construct(
        private readonly IParKingLotService $parKingLot
    )
    {
    }

    public function index()
    {
//        return "cong";
         return $this->parKingLot->getAllParkingLot(true);
    }

}
