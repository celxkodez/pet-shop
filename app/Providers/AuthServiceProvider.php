<?php

namespace App\Providers;

// use Illuminate\Support\Facades\Gate;
use App\Services\AuthService;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        \Auth::extend('jwt', function ($app, $name, array $config) {
            return new AuthService(\Auth::createUserProvider($config['provider']), $app->make('request'));
        });
    }
}
