<?php

namespace App\Providers;

use App\Http\Controllers\FriendController;
use App\Services\Implements\AccountService;
use App\Services\Implements\AuthService;
use App\Services\Implements\JWTService;
use App\Services\Implements\MailService;
use App\Services\Implements\OTPService;
use App\Services\Implements\RedisService;
use App\Services\Interfaces\IAccountService;
use App\Services\Interfaces\IAuthService;
use App\Services\Interfaces\IJWTService;
use App\Services\Interfaces\IMailService;
use App\Services\Interfaces\IOTPService;
use App\Services\Interfaces\IRedisService;
use Illuminate\Support\ServiceProvider;
 
class ServiceLayerProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(
            IAuthService::class,
            AuthService::class
        );
        $this->app->singleton(
            IJWTService::class,
            JWTService::class
        );
        $this->app->singleton(
            IMailService::class,
            MailService::class
        );
        $this->app->singleton(
            IOTPService::class,
            OTPService::class
        );
        $this->app->singleton(
            IRedisService::class,
            RedisService::class
        );
        $this->app->singleton(
            IAccountService::class,
            AccountService::class
        );
         
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
