<?php

namespace App\Services\Interfaces;

interface IProfile
{
    public function show(int $id):array;
    public function editProfile(int $id,array $info);

}
