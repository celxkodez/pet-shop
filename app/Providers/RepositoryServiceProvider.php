<?php

namespace App\Providers;

use App\Contracts\RepositoryInterfaces\OrderRepositoryContract;
use App\Repositories\OrderRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    protected array $repositoryBindings = [
        OrderRepositoryContract::class => OrderRepository::class,
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
