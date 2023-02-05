<?php

namespace App\Http\Controllers\Account;

use App\Http\Requests\AuthRequest;
use App\Services\Interfaces\IAuthService;
use App\Utils\CookieGenerator;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function __construct(private readonly IAuthService $authService) {}
    public function login(AuthRequest $request): JsonResponse
    {
        $email = $request->input("email");
        $password = $request->input("password");
        $loginData = $this->authService->login($email, $password);
        if ($loginData) {
            ["accessToken" => $accessToken, "refreshToken" => $refreshToken, "uid" => $uid, "fullName" => $fullName] = $loginData;
            $response = $this->responseSuccessWithData(
                "login.successful",
                compact("accessToken", "uid", "fullName")
            );
            $refreshTokenCookie = CookieGenerator::generateRefreshTokenCookie($refreshToken);
            return $response->cookie($refreshTokenCookie);
        }
        return $this->responseErrorWithDetails(
            "login.failed",
            ["error" => "Email or password wrong!"],
            Response::HTTP_UNAUTHORIZED
        );
    }
}
