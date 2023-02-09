<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Services\Interfaces\IProfile;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly IProfile $profileService,
    ) {}
    public function showProfile($id){
        $userInfo= $this->profileService->show($id);
        if($userInfo == [null]){
            return $this->responseError(
                "User not exit !",
                Response::HTTP_BAD_REQUEST,
            );
        }
        else{
            return $this->responseSuccessWithData(
            "Infomation of user",
            $userInfo,
            Response::HTTP_ALREADY_REPORTED
        );
        }
    }
    public function updateProfile(){



    }
}
