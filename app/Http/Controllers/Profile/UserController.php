<?php

namespace App\Http\Controllers\Profile;

use App\Http\Controllers\Clound\CloudinaryStorage;
use App\Http\Controllers\Controller;
use App\Http\Requests\ProfileRequest;
use App\Models\User;
use App\Services\Interfaces\IProfile;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct(
        private readonly IProfile $profileService,
    ) {}
    public function getAllUser(){
        $userData=$this->profileService->getAllUser();
        return $userData;
    }
    public function getRole($id){
        $role['role'] = User::find($id)->role;
        return $role;
    }
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
    public function updateProfile(Request $request,$id){
        $validator = Validator::make($request->all(), [
            'fullName' => 'required',
            'avatar' => 'required|image|max:2048',
        ]);
        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }
        $dateData = $validator->validated();

        $user= User::find($id);


        $user->fullName=$dateData["fullName"];
        if ($request->hasFile('avatar')) {
            $file   = $request->file('avatar');
            $linkImage =CloudinaryStorage::upload($file->getRealPath(), $file->getClientOriginalName(),'account/profile'); 
            $user->avatar = $linkImage;
        }
        $user->save();
        return $this->responseSuccessWithData("update success",[$user],Response::HTTP_OK);

    }
   

}
