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
    public function showProfile(Request $request){
        $id =$request->input('id');
        $userInfo= $this->profileService->show($id);
        return $userInfo;
        // return $this->responseSuccessWithData(
        //     "Infomation of user",
        //     $userInfo,
        //     Response::HTTP_ALREADY_REPORTED
        // );
    }
    public function updateProfile(){



    }
}
