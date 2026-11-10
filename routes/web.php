<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\LockScreenController;
use Illuminate\Support\Facades\Route;
use Rap2hpoutre\LaravelLogViewer\LogViewerController;
use App\Http\Controllers\Account\PasswordController;
use App\Http\Controllers\Admin\DatabaseBackupController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseTypeController;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\MonthEndDashboardController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\UserPermissionController;
use App\Http\Controllers\VendorController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\PaymentPlatformController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/logs', [LogViewerController::class, 'index'])
    ->middleware(['auth', 'locked'])
    ->name('logs');

Route::middleware(['auth', 'locked', 'verified'])->prefix('auth')->group(function () {
    Route::get('/dashboard1', [DashboardController::class, 'dashboard1'])->name('dashboard1');
    Route::get('/dashboard2', [DashboardController::class, 'dashboard2'])->name('dashboard2');
    Route::get('/new-dashboard', [DashboardController::class, 'dashboard3'])->name('dashboard3');
    Route::get('/dashboard/summary', [DashboardController::class, 'summary'])->name('dashboard.summary');
});

Route::middleware(['auth', 'locked'])->group(function () {
    Route::prefix('dashboard')->group(function () {
        Route::get('summary', [DashboardController::class, 'summary']);
        Route::get('day-wise', [DashboardController::class, 'dayWise']);
        Route::get('day-wise-category', [DashboardController::class, 'dayWiseWithCategory']);
        Route::get('category-wise', [DashboardController::class, 'categoryWise']);
        Route::get('monthly-trend', [DashboardController::class, 'monthlyTrend']);
        Route::get('payment-method-wise', [DashboardController::class, 'paymentMethodWise']);
        Route::get('expense-type-wise', [DashboardController::class, 'expenseTypeWise']);
        Route::get('comparison', [DashboardController::class, 'comparison']);
        Route::get('complete', [DashboardController::class, 'complete']);
    });

    Route::get('/month-end-dashboard', [MonthEndDashboardController::class, 'index'])
        ->name('month-end.index');
    Route::get('/month-end-dashboard/v2', [MonthEndDashboardController::class, 'v2'])
        ->name('month-end.v2');
    Route::get('/month-end-dashboard/card/{card}', [MonthEndDashboardController::class, 'cardDetails'])
        ->name('month-end.card');
    Route::get('/month-end-dashboard/card/{card}/export', [MonthEndDashboardController::class, 'exportCard'])
        ->name('month-end.card.export');
    Route::post('/month-end-dashboard/budgets', [MonthEndDashboardController::class, 'storeBudget'])
        ->name('month-end.budgets.store');

    Route::prefix('reports')->group(function () {
        Route::get('/', [ReportsController::class, 'index'])->name('reports.index');
        Route::get('spending-insights', [ReportsController::class, 'spendingInsights']);
        Route::get('detailed', [ReportsController::class, 'detailedReport']);
        Route::get('category', [ReportsController::class, 'categoryReport']);
        Route::get('payment-method', [ReportsController::class, 'paymentMethodReport']);
        Route::get('monthly-summary', [ReportsController::class, 'monthlySummaryReport']);
        Route::get('custom', [ReportsController::class, 'customReport']);
        Route::get('export-csv', [ReportsController::class, 'exportCsv']);
        Route::get('export-pdf', [ReportsController::class, 'exportPdf']);
        Route::get('export-excel', [ReportsController::class, 'exportExcel']);
    });

    Route::get('/admin/db-backup', [DatabaseBackupController::class, 'index'])->name('db.backup');
    Route::post('/db-backup', [DatabaseBackupController::class, 'store'])->name('db.backup.store');
    Route::get('/db-backup/download/{file}', [DatabaseBackupController::class, 'download'])->name('db.backup.download');
    
});


Route::middleware(['auth', 'locked'])->group(function () {

    /**
     * Profile
     */
    Route::prefix('profile')
        ->name('profile.')
        ->controller(ProfileController::class)
        ->group(function () {
            Route::get('/', 'showProfile')->name('edit');              // profile.edit
            Route::patch('/', 'update')->name('update');               // profile.update
            Route::delete('/', 'destroy')->name('destroy');            // profile.destroy
            Route::put('/two-factor-auth', 'twoFactorAuth')
                ->name('two.factor.auth');                             // profile.two.factor.auth
        });

    /**
     * Password
     */
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    /**
     * Core RBAC Resources
     */
    Route::resource('roles', RoleController::class)->except(['show']);
    Route::get('roles/list', [RoleController::class, 'getAll'])->name('roles.list');
    Route::get('users/list', [UserController::class, 'list'])->name('users.list');
    Route::resource('users', UserController::class)->except(['show']);

    Route::resource('permissions', PermissionController::class)->except(['show']);
    Route::get('permissions/list', [PermissionController::class, 'getAll'])->name('permissions.list');
    /**
     * 
     * User Permissions
     */
    Route::prefix('users/{user}')
        ->name('users.')
        ->group(function () {
            Route::get('permissions', [UserPermissionController::class, 'edit'])
                ->name('permissions');            // users.permissions
            Route::put('permissions', [UserPermissionController::class, 'update'])
                ->name('permissions.update');     // users.permissions.update
        });

    /**
     * Master – Categories
     */
    Route::prefix('category')
        ->name('category.')
        ->controller(CategoryController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/classification', 'classification')->name('classification');
            Route::patch('/{category}/classification', 'updateClassification')->name('classification.update');
            Route::get('/{category}/edit', 'edit')->name('edit');
            Route::put('/{category}', 'update')->name('update');
            Route::delete('/{category}', 'destroy')->name('destroy');
        });


    /**
     * Master – Expense Types
     */
    Route::prefix('expense-type')
        ->name('expensetype.')
        ->controller(ExpenseTypeController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');                   // expensetype.index
            Route::get('/getAll', 'getAll')->name('list');             // expensetype.list
            Route::get('/create', 'create')->name('create');           // expensetype.create
            Route::post('/store', 'store')->name('store');             // expensetype.store
            Route::get('/{expenseType}/edit', 'edit')->name('edit');            // expensetype.edit
            Route::put('/{expenseType}', 'update')->name('update');             // expensetype.update
            Route::delete('/{expenseType}', 'destroy')->name('destroy');        // expensetype.destroy
        });

    /**
     * Master – Payment Method
     */
    Route::prefix('payment-method')
        ->name('paymentmethod.')
        ->controller(PaymentMethodController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');                   // paymentmethod.index
            Route::get('/getAll', 'getAll')->name('list');             // paymentmethod.list 
            Route::get('/create', 'create')->name('create');           // paymentmethod.create
            Route::post('/store', 'store')->name('store');             // paymentmethod.store
            Route::get('/{paymentMethod}/edit', 'edit')->name('edit');            // paymentmethod.edit
            Route::put('/{paymentMethod}', 'update')->name('update');             // paymentmethod.update
            Route::delete('/{paymentMethod}', 'destroy')->name('destroy');        // paymentmethod.destroy
        });

    /**
     * Accounts and payment platforms
     */
    Route::prefix('account')->name('account.')->controller(AccountController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/list', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{account}/edit', 'edit')->name('edit');
        Route::put('/{account}', 'update')->name('update');
        Route::delete('/{account}', 'destroy')->name('destroy');
    });

    Route::prefix('payment-platform')->name('payment-platform.')->controller(PaymentPlatformController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::get('/list', 'list')->name('list');
        Route::get('/create', 'create')->name('create');
        Route::post('/store', 'store')->name('store');
        Route::get('/{paymentPlatform}/edit', 'edit')->name('edit');
        Route::put('/{paymentPlatform}', 'update')->name('update');
        Route::delete('/{paymentPlatform}', 'destroy')->name('destroy');
    });

    /**
     * Master – Vendors
     */
    Route::prefix('vendor')
        ->name('vendor.')
        ->controller(VendorController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/list', 'list')->name('list');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');
            Route::get('/{vendor}/edit', 'edit')->name('edit');
            Route::put('/{vendor}', 'update')->name('update');
            Route::delete('/{vendor}', 'destroy')->name('destroy');
        });

    /**
     * Master – Menu
     */
    Route::prefix('menu')
        ->name('menu.')
        ->controller(MenuController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');
            Route::get('/getAll', 'getAll')->name('list');
            Route::get('/create', 'create')->name('create');
            Route::post('/store', 'store')->name('store');

            Route::get('/{menu}/edit', 'edit')->name('edit');
            Route::put('/{menu}', 'update')->name('update');
            Route::delete('/{menu}', 'destroy')->name('destroy');
        });


    /**
     * Expense
     */
    Route::prefix('expense')
        ->name('expense.')
        ->controller(ExpenseController::class)
        ->group(function () {
            Route::get('/', 'index')->name('index');                   // paymentmethod.index
            Route::get('/getAll', 'getAll')->name('list');             // paymentmethod.list
            Route::get('/import/sample', 'downloadImportSample')->name('import.sample');
            Route::post('/import', 'import')->name('import');
            Route::get('/create', 'create')->name('create');           // paymentmethod.create
            Route::post('/store', 'store')->name('store');             // paymentmethod.store
            Route::get('/{expense}/edit', 'edit')->name('edit');       // paymentmethod.edit
            Route::put('/{expense}', 'update')->name('update');        // paymentmethod.update
            Route::delete('/{expense}', 'destroy')->name('destroy');   // paymentmethod.destroy
        });
});

Route::middleware(['auth'])->group(function () {
    Route::get('lock', [LockScreenController::class, 'show'])->name('lock.show');
    Route::post('lock/unlock', [LockScreenController::class, 'unlock'])->name('lock.unlock');
    Route::post('lock/lock', [LockScreenController::class, 'lock'])->name('lock.lock');
    Route::post('logout', [AuthController::class, 'destroy'])->name('logout');
});

require __DIR__ . '/auth.php';
