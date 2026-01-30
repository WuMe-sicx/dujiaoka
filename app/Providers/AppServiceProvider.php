<?php

namespace App\Providers;

use App\Service\BalanceService;
use App\Service\CarmisService;
use App\Service\CouponService;
use App\Service\EmailtplService;
use App\Service\GoodsService;
use App\Service\OrderProcessService;
use App\Service\OrderService;
use App\Service\PayService;
use App\Service\PurchaseLimitService;
use Illuminate\Support\ServiceProvider;
use Jenssegers\Agent\Agent;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('Service\GoodsService', function () {
            return $this->app->make(GoodsService::class);
        });
        $this->app->singleton('Service\PayService', function () {
            return $this->app->make(PayService::class);
        });
        $this->app->singleton('Service\CarmisService', function () {
            return $this->app->make(CarmisService::class);
        });
        $this->app->singleton('Service\OrderService', function () {
            return $this->app->make(OrderService::class);
        });
        $this->app->singleton('Service\CouponService', function () {
            return $this->app->make(CouponService::class);
        });
        $this->app->singleton('Service\OrderProcessService', function () {
            return $this->app->make(OrderProcessService::class);
        });
        $this->app->singleton('Service\EmailtplService', function () {
            return $this->app->make(EmailtplService::class);
        });
        $this->app->singleton('Service\PurchaseLimitService', function () {
            return $this->app->make(PurchaseLimitService::class);
        });
        $this->app->singleton('Service\BalanceService', function () {
            return $this->app->make(BalanceService::class);
        });
        $this->app->singleton('Jenssegers\Agent', function () {
            return $this->app->make(Agent::class);
        });

    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        \App\Models\Carmis::observe(\App\Observers\CarmisObserver::class);
    }
}
