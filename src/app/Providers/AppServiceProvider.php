<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;
use App\Actions\Fortify\LoginResponse;

class AppServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->singleton(LoginResponseContract::class, LoginResponse::class);
        Paginator::useBootstrap();

    }
}