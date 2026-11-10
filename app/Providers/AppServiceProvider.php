<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Interfaces\CategoryRepositoryInterface;
use App\Interfaces\ExpenseTypeRepositoryInterface;
use App\Interfaces\PaymentMethodRepositoryInterface;
use App\Interfaces\ExpenseRepositoryInterface;
use App\Interfaces\MenuRepositoryInterface;
use App\Interfaces\PermissionRepositoryInterface;
use App\Interfaces\RoleRepositoryInterface;
use App\Interfaces\UserRepositoryInterface;
use App\Interfaces\BackupRepositoryInterface;
use App\Models\Permission;
use App\Models\User;
use App\Observers\PermissionObserver;
use App\Repositories\BackupRepository;
use App\Repositories\CategoryRepository;
use App\Repositories\ExpenseTypeRepository;
use App\Repositories\PaymentMethodRepository;
use App\Repositories\ExpenseRepository;
use App\Repositories\MenuRepository;
use App\Repositories\PermissionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UserRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CategoryRepositoryInterface::class,
            CategoryRepository::class
        );

        $this->app->bind(
            ExpenseTypeRepositoryInterface::class,
            ExpenseTypeRepository::class
        );
        $this->app->bind(
            PaymentMethodRepositoryInterface::class,
            PaymentMethodRepository::class
        );
        $this->app->bind(
            ExpenseRepositoryInterface::class,
            ExpenseRepository::class
        );

        $this->app->bind(
            MenuRepositoryInterface::class,
            MenuRepository::class
        );
        
        $this->app->bind(
            PermissionRepositoryInterface::class,
            PermissionRepository::class
        );

        $this->app->bind(
            RoleRepositoryInterface::class,
            RoleRepository::class
        );

        $this->app->bind(
            UserRepositoryInterface::class,
            UserRepository::class
        );
        $this->app->bind(
            BackupRepositoryInterface::class,
            BackupRepository::class
        );
        
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        DB::prohibitDestructiveCommands(
            app()->isProduction()
        );
        Gate::before(function (User $user, string $ability) {
            if ($user->isSuperAdmin()) {
                return true; // Grant all access
            }
        });
        Permission::observe(PermissionObserver::class);
    }
}
