<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Http\Requests\OTPRegisterRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IMailService;
use App\Services\Interfaces\IOTPService;
use App\Services\Interfaces\IRedisService;
use App\Services\Interfaces\MailType;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RegisterController extends Controller
{
    public function __construct(
        private readonly IAccountService $accountService,
        private  readonly  IOTPService $otpService,
        private readonly IRedisService $redisService,
        private readonly IMailService $mailService

    ) {}
    public function register(RegisterRequest $request){
        $userData = $request->validated();
        $otp = rand(100000,999999);
        $user = new User;
        $user['email'] = $userData['email'];

        $this->otpService->sendOTP($user, $otp);

        $this->redisService->setOtp($user['email'],$otp);
        $this->redisService->setInfoRegis($userData,$otp);

    }
    public function confirmRegistration(OTPRegisterRequest $request) {
            $userData = $request->validated();

            $optConfirm = $this->redisService->getOtp($userData['email']);

            if($optConfirm == null){
                return $this->responseError(
                    "Email not sign up !",
                    Response::HTTP_BAD_REQUEST,
                );
            }
            elseif ($userData['otp'] == $optConfirm){
                        $user =  $this->redisService->getInfoRegis($userData['otp']);

                        $accData = $this->accountService->register((array)$user);


                        $this->redisService->deleteOtp($user->email);
                        $this->redisService->deleteInfor($userData['otp']);

                        $this->mailService->sendMail(MailType::WELCOME_MAIL,['email'=>$userData['email']]);
                        return $this->responseSuccessWithData(
                        "Create a new account successfully!",
                        $accData,
                        Response::HTTP_CREATED
                        );
            }
            else{
                return $this->responseError(
                    "OTP Invalid!",
                     Response::HTTP_BAD_REQUEST
                );
            }
    }
}
