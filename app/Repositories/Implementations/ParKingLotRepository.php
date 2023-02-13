<?php

namespace App\Repositories\Implementations;
use App\Models\ParkingLot;
use \App\Repositories\Interfaces\IParKingLotRepository;
class ParKingLotRepository extends BaseRepository implements IParKingLotRepository
{
    public function getModel(): string
    {
        return ParkingLot::class;
    }

    public function showInfo(int $id): mixed
    {
        $info = $this->model->find($id,
            ["id", "nameParkingLot", "image",'address','openTime',
                'endTime','desc','status']);
        return $info ? : null;
    }
    public function showInforOfParking(int $id): mixed
    {
        
    }

}
