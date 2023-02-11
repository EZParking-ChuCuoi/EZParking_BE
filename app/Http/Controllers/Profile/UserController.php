<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Services\Interfaces\IProfile;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
            [$userInfo],
            Response::HTTP_ALREADY_REPORTED
        );
        }
    }
    public function updateProfile(ProfileRequest $request,$id){
        $userData = $request->validated();
         
        $user= User::find($id);
       
        $user->fullName=$request->fullName;
        try {
            if (!$request->hasFile('avatar')) {
                return "Avatar require!";
            }
            $response = Cloudinary::upload($request->file('avatar')->getRealPath())->getSecurePath();
            $user->avatar=$response;
           

        } catch (\Exception $e) {
            return '$this->returnError(201, $e->getMessage())';
        }
        $user->save();
        return $user;

    }
}
