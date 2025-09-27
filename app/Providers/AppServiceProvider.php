<?php

namespace App\Providers;
use App\Domains\Shared\Repositories\UserRepository;
use App\Domains\Shared\Repositories\UserRepositoryInterface;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });
        RateLimiter::for('register', function (Request $request) {
            return Limit::perMinute(5)->by($request->ip()); // حداکثر ۵ ثبت‌نام در دقیقه از یک IP
        });
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip()); // حداکثر ۱۰ لاگین در دقیقه
        });
    }
}
