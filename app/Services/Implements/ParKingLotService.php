<?php

namespace App\Services\Implements;

use App\Repositories\Interfaces\IParKingLotRepository;

class ParKingLotService implements \App\Services\Interfaces\IParKingLotService
{
    public function __construct(
        private readonly IParKingLotRepository $parKingLotRepository,
    )
    {
    }

    public function getAllParkingLot(): array|null
    {
        return $this->parKingLotRepository->all();
    }

    public function getParkingLotById(int $id): array|null
    {
        return $this->parKingLotRepository->showInfo($id);
    }

    public function editParKingLot(int $id, array $info): mixed
    {
        return true;
    }
}
