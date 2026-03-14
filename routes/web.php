<?php

use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\AdminOwnerSubscriptionController;
use App\Http\Controllers\Client\AdminUserController;
use App\Http\Controllers\Client\ClientPanelController;
use App\Http\Controllers\Client\ExportController;
use App\Http\Controllers\Client\InvoiceController;
use App\Http\Controllers\Client\InvoiceLineController;
use App\Http\Controllers\Client\LeaseChargeRuleController;
use App\Http\Controllers\Client\LeaseController;
use App\Http\Controllers\Client\MeterController;
use App\Http\Controllers\Client\MeterReadingController;
use App\Http\Controllers\Client\NewPasswordController;
use App\Http\Controllers\Client\OwnerBillingController;
use App\Http\Controllers\Client\OwnerPropertyController;
use App\Http\Controllers\Client\PaymentController;
use App\Http\Controllers\Client\PasswordResetLinkController;
use App\Http\Controllers\Client\ProfileController;
use App\Http\Controllers\Client\PropertyUnitController;
use App\Http\Controllers\Client\ReportController;
use App\Http\Controllers\Client\TenantInvoiceController;
use App\Http\Controllers\Client\TenantLeaseController;
use App\Http\Controllers\Client\TenantMeterController;
use App\Http\Controllers\Client\TenantProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/client/login')->name('login');

Route::prefix('client')
    ->middleware('client.guest')
    ->group(function (): void {
        Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
        Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');
        Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
        Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
    });

Route::prefix('client')
    ->as('client.')
    ->group(function (): void {
        Route::get('/', function () {
            return Auth::check()
                ? redirect()->route('client.panel')
                : redirect()->route('client.login');
        })->name('index');

        Route::middleware('client.guest')->group(function (): void {
            Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
            Route::post('/login', [AuthController::class, 'login'])->name('login.store');
            Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
            Route::post('/register', [AuthController::class, 'register'])->name('register.store');
        });

        Route::middleware(['auth', 'role:admin,owner,tenant'])->group(function (): void {
            Route::get('/panel', ClientPanelController::class)->name('panel');
            Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
            Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
            Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password');
            Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
            Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
        });

        Route::middleware(['auth', 'role:owner'])->group(function (): void {
            Route::get('abonements', [OwnerBillingController::class, 'index'])->name('billing.index');
            Route::post('abonements/checkout', [OwnerBillingController::class, 'checkout'])->name('billing.checkout');
            Route::post('abonements/swap', [OwnerBillingController::class, 'swap'])->name('billing.swap');
            Route::get('abonements/portal', [OwnerBillingController::class, 'portal'])->name('billing.portal');
            Route::get('abonements/success', [OwnerBillingController::class, 'success'])->name('billing.success');
            Route::get('abonements/cancel', [OwnerBillingController::class, 'cancel'])->name('billing.cancel');

            Route::resource('ipasumi', OwnerPropertyController::class)
                ->parameters(['ipasumi' => 'property'])
                ->names([
                    'index' => 'properties.index',
                    'create' => 'properties.create',
                    'store' => 'properties.store',
                    'show' => 'properties.show',
                    'edit' => 'properties.edit',
                    'update' => 'properties.update',
                    'destroy' => 'properties.destroy',
                ]);

            Route::resource('vienibas', PropertyUnitController::class)
                ->except('show')
                ->parameters(['vienibas' => 'unit'])
                ->names('units');

            Route::resource('irnieki', TenantProfileController::class)
                ->except('show')
                ->parameters(['irnieki' => 'tenant'])
                ->names('tenants');

            Route::resource('ligumi', LeaseController::class)
                ->parameters(['ligumi' => 'lease'])
                ->names('leases');
            Route::post('ligumi/{lease}/generate-invoice', [LeaseController::class, 'generateInvoice'])->name('leases.generate-invoice');

            Route::post('ligumi/{lease}/charge-rules', [LeaseChargeRuleController::class, 'store'])->name('charge-rules.store');
            Route::get('ligumi/{lease}/charge-rules/{chargeRule}/edit', [LeaseChargeRuleController::class, 'edit'])->name('charge-rules.edit');
            Route::put('ligumi/{lease}/charge-rules/{chargeRule}', [LeaseChargeRuleController::class, 'update'])->name('charge-rules.update');
            Route::delete('ligumi/{lease}/charge-rules/{chargeRule}', [LeaseChargeRuleController::class, 'destroy'])->name('charge-rules.destroy');

            Route::resource('rekini', InvoiceController::class)
                ->only(['index', 'show', 'update', 'destroy'])
                ->parameters(['rekini' => 'invoice'])
                ->names('invoices');
            Route::post('rekini/{invoice}/issue', [InvoiceController::class, 'issue'])->name('invoices.issue');
            Route::post('rekini/{invoice}/send', [InvoiceController::class, 'send'])->name('invoices.send');
            Route::post('rekini/{invoice}/remind', [InvoiceController::class, 'remind'])->name('invoices.remind');
            Route::post('rekini/{invoice}/cancel', [InvoiceController::class, 'cancel'])->name('invoices.cancel');
            Route::get('rekini/{invoice}/download', [InvoiceController::class, 'download'])->name('invoices.download');
            Route::get('rekini/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
            Route::post('rekini/{invoice}/payments', [PaymentController::class, 'store'])->name('payments.store');
            Route::post('rekini/{invoice}/lines', [InvoiceLineController::class, 'store'])->name('invoice-lines.store');
            Route::put('rekini/{invoice}/lines/{line}', [InvoiceLineController::class, 'update'])->name('invoice-lines.update');
            Route::delete('rekini/{invoice}/lines/{line}', [InvoiceLineController::class, 'destroy'])->name('invoice-lines.destroy');

            Route::resource('skaititaji', MeterController::class)
                ->parameters(['skaititaji' => 'meter'])
                ->names('meters');
            Route::post('skaititaji/{meter}/readings', [MeterReadingController::class, 'store'])->name('meter-readings.store');

            Route::get('atskaites', [ReportController::class, 'index'])->name('reports.index');
            Route::get('atskaites/eksports', [ReportController::class, 'export'])->name('reports.export');
            Route::get('eksports', [ExportController::class, 'index'])->name('exports.index');
            Route::post('eksports', [ExportController::class, 'download'])->name('exports.download');
        });

        Route::middleware(['auth', 'role:admin'])->group(function (): void {
            Route::get('users', [AdminUserController::class, 'index'])->name('admin.users.index');
            Route::get('users/{user}/edit', [AdminUserController::class, 'edit'])->name('admin.users.edit');
            Route::put('users/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
            Route::delete('users/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
            Route::post('users/{user}/send-password-reset', [AdminUserController::class, 'sendPasswordReset'])->name('admin.users.send-password-reset');
            Route::get('owner-subscriptions', [AdminOwnerSubscriptionController::class, 'index'])->name('admin.owner-subscriptions.index');
            Route::put('owner-subscriptions/{owner}', [AdminOwnerSubscriptionController::class, 'update'])->name('admin.owner-subscriptions.update');
            Route::post('owner-subscriptions/plans', [AdminOwnerSubscriptionController::class, 'storePlan'])->name('admin.owner-subscriptions.plans.store');
            Route::put('owner-subscriptions/plans/{plan}', [AdminOwnerSubscriptionController::class, 'updatePlan'])->name('admin.owner-subscriptions.plans.update');
            Route::delete('owner-subscriptions/plans/{plan}', [AdminOwnerSubscriptionController::class, 'destroyPlan'])->name('admin.owner-subscriptions.plans.destroy');
        });

        Route::middleware(['auth', 'role:tenant'])->group(function (): void {
            Route::get('mani-ligumi', [TenantLeaseController::class, 'index'])->name('tenant-leases.index');
            Route::get('mani-rekini', [TenantInvoiceController::class, 'index'])->name('tenant-invoices.index');
            Route::get('mani-rekini/{invoice}', [TenantInvoiceController::class, 'show'])->name('tenant-invoices.show');
            Route::get('mani-rekini/{invoice}/download', [TenantInvoiceController::class, 'download'])->name('tenant-invoices.download');
            Route::get('mani-rekini/{invoice}/print', [TenantInvoiceController::class, 'print'])->name('tenant-invoices.print');
            Route::get('mani-skaititaji', [TenantMeterController::class, 'index'])->name('tenant-meters.index');
            Route::post('mani-skaititaji/{meter}/readings', [TenantMeterController::class, 'store'])->name('tenant-meter-readings.store');
        });
    });
