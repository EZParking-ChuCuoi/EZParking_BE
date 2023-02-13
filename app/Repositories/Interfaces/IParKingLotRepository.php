<?php

namespace App\Repositories\Interfaces;

interface IParKingLotRepository extends IRepository
{
    public function showInfo(int $id):mixed;
}
