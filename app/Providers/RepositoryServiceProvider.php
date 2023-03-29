<?php

namespace App\Providers;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Contracts\RepositoryInterfaces\UserRepositoryContract;
use App\Repositories\OrderRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositoryBindings = [
        OrderRepositoryContract::class => OrderRepository::class,
        UserRepositoryContract::class => UserRepository::class,
    ];
    /**
     * Register services.
     */
    public function register(): void
    {
        foreach ($this->repositoryBindings as $contract => $implementation) {
            $this->app->bind($contract,$implementation);
        }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
