<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

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
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app['events']->listen(
            \Illuminate\Mail\Events\MessageSending::class,
            \App\Listeners\RedirectStagingMail::class
        );
        Schema::defaultStringLength(191);
         Paginator::useBootstrap();
    }
}
