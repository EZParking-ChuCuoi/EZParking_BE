<?php
namespace App\Services\Implements;

use App\Repositories\Interfaces\IUserRepository;
use App\Services\Interfaces\IAccountService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;


class AccountService implements  IAccountService {
    public function __construct(
        private readonly IUserRepository $userRepository
    )
    {}

    public function register(array $data): array
      {

          $data['fullName'] = $data['fullName'];
          $data['password'] = Hash::make($data['password']);
          $data['status'] = 0;
          $data['avatar'] = fake()->imageUrl();
          $this->userRepository->create($data);
          return ["fullName" => $data["fullName"], "email" => $data["email"]];
      }
      public function forgotPassword()
      {
          // TODO: Implement forgotPassword() method.
      }
      public function validateForgotPasswordToken(Request $request)
      {
          // TODO: Implement validateForgotPasswordToken() method.
      }

      public function  updateInformation()
      {
          // TODO: Implement updateInformation() method.
      }
}
