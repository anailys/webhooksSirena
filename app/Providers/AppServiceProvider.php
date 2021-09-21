<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Sirena;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
        $this->app->bind('Sirena',function($app){
            return new Sirena;
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
