<?php

use App\Http\Controllers\Api\Admin\AdminContributionController;
use App\Http\Controllers\Api\Admin\AdminDashboardController;
use App\Http\Controllers\Api\Admin\AdminPensionPlanController;
use App\Http\Controllers\Api\Admin\AdminUserController;
use App\Http\Controllers\Api\Admin\AdminWithdrawalController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ContributionController;
use App\Http\Controllers\Api\PensionPlanController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\WithdrawalController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/admin/login', [AuthController::class, 'adminLogin']);

Route::middleware('api.token')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/profile', [ProfileController::class, 'show']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/dashboard', [ProfileController::class, 'dashboard']);
    Route::get('/pension-plans', [PensionPlanController::class, 'index']);
    Route::get('/contributions', [ContributionController::class, 'index']);
    Route::post('/contributions', [ContributionController::class, 'store']);
    Route::post('/contributions/initialize-payment', [ContributionController::class, 'initializePayment']);
    Route::post('/contributions/verify-payment', [ContributionController::class, 'verifyPayment']);
    Route::get('/withdrawals', [WithdrawalController::class, 'index']);
    Route::post('/withdrawals', [WithdrawalController::class, 'store']);

    Route::prefix('admin')->middleware('admin')->group(function () {
        Route::get('/dashboard', [AdminDashboardController::class, 'index']);
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::apiResource('/pension-plans', AdminPensionPlanController::class);
        Route::get('/contributions', [AdminContributionController::class, 'index']);
        Route::get('/withdrawals', [AdminWithdrawalController::class, 'index']);
        Route::patch('/withdrawals/{withdrawal}/approve', [AdminWithdrawalController::class, 'approve']);
        Route::patch('/withdrawals/{withdrawal}/reject', [AdminWithdrawalController::class, 'reject']);
        Route::get('/reports', [AdminDashboardController::class, 'reports']);
    });
});
