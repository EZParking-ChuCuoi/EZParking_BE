<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPassWordRequest;
use App\Models\User;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IOTPService;
use App\Services\Interfaces\IRedisService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForgotPasswordController extends Controller
{
    public function __construct(
        private readonly IAccountService $accountService,
        private  readonly  IOTPService $otpService,
        private readonly IRedisService $redisService,
    ) {}

    public function sendCode(ForgotPasswordRequest $request): JsonResponse{
        $userData = $request->validated();
        $otp = rand(100000,999999);

        $user = new User();
        $user['email'] = $userData['email'];
        $this->otpService->sendOTP($user, $otp);
        $this->redisService->setOtp($user['email'],$otp);
        $this->redisService->setInfoRegis($userData,$otp);


        return $this->responseSuccessWithData(
            "Check email to get code!",
            $userData,
            Response::HTTP_CREATED
            );

    }

    public function checkCode(Request $request): JsonResponse {

        $opt = $request->input('otp');
        $email = $request->input('email');
        $optConfirm = $this->redisService->getOtp($email);
        if($optConfirm == null){
            return $this->responseError(
                "Email not fould !",
                Response::HTTP_BAD_REQUEST,
            );
        }
        elseif ($opt == $optConfirm){
            $user =  $this->redisService->getInfoRegis($opt);

            $this->redisService->deleteOtp($user->email);
            $this->redisService->deleteInfor($opt);
            return $this->responseSuccessWithData(
                "OTP Matching ",
                ["email"=>$email],
                 Response::HTTP_OK,
            );
        }
        else {
            return $this->responseError(
                "OTP Invalid!",
                 Response::HTTP_BAD_REQUEST
            );
        }
    }

    public function resetPassword(ResetPassWordRequest $request): JsonResponse {
        
        $userData = $request->validated();
        $this->accountService->resetPassword($userData);
        return $this->responseSuccess("Change password success!",Response::HTTP_OK);
    }

}
