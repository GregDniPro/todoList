<?php

declare(strict_types=1);

namespace App\Providers;

use App\Repositories\Interfaces\TasksRepositoryInterface;
use App\Repositories\Interfaces\UsersRepositoryInterface;
use App\Repositories\TasksRepository;
use App\Repositories\UsersRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            TasksRepositoryInterface::class,
            TasksRepository::class
        );

        $this->app->bind(
            UsersRepositoryInterface::class,
            UsersRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
    }
}
