<?php

use App\Http\Controllers\Client\AuthController;
use App\Http\Controllers\Client\ClientPanelController;
use App\Http\Controllers\Client\OwnerPropertyController;
use App\Http\Controllers\Client\ProfileController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::redirect('/login', '/client/login')->name('login');

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
            Route::resource('ipasumi', OwnerPropertyController::class)
                ->except('show')
                ->parameters(['ipasumi' => 'property'])
                ->names([
                    'index' => 'properties.index',
                    'create' => 'properties.create',
                    'store' => 'properties.store',
                    'edit' => 'properties.edit',
                    'update' => 'properties.update',
                    'destroy' => 'properties.destroy',
                ]);
        });
    });
