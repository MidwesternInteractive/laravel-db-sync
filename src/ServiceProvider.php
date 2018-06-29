<?php
namespace MWI\DBSync;
use App\Observers\UserObserver;
use App\User;
use Form;
use Illuminate\Support\ServiceProvider as LaravelServiceProvider;
class ServiceProvider extends LaravelServiceProvider
{
    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MWI\Commands\DBSync::class
            ]);
        }
    }
}