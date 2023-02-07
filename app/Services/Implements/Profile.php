<?php

namespace App\Services\Implements;

use App\Repositories\Interfaces\IUserRepository;
use Illuminate\Support\Facades\Hash;

class Profile implements \App\Services\Interfaces\IProfile
{
    public function __construct(
        private readonly IUserRepository $userRepository
    )
    {}

    public function show(int $id): array
    {
        return $this->userRepository->getInfo($id);

    }

    public function editProfile(int $id,array $info)
    {
        $data['fullName'] = $info['fullName'];
        $this->userRepository->create($data);
        return ["fullName" => $data["fullName"], "email" => $data["email"]];
        return $this->userRepository->update($id,$info);
    }
}
