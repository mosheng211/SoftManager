<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\UserController;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\RechargeController;
use App\Http\Controllers\API\PaymentController;
use App\Http\Controllers\API\DistributorController;
use App\Http\Controllers\API\Admin\UserController as AdminUserController;
use App\Http\Controllers\API\Admin\DistributorController as AdminDistributorController;
use App\Http\Controllers\API\Admin\SettingController as AdminSettingController;
use App\Http\Controllers\API\Admin\PaymentController as AdminPaymentController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// 公共API路由
Route::get('/users/{id}', [UserController::class, 'show']);

// 支付回调
Route::get('/payments/return', [PaymentController::class, 'handleReturn']);
Route::get('/payments/notify', [PaymentController::class, 'handleNotify']);

// 认证相关路由
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);

// 需要认证的API路由
Route::middleware(['auth:sanctum', 'single.device'])->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    
    // 充值相关路由
    Route::get('/user/recharge/plans', [RechargeController::class, 'getPlans']);
    Route::post('/user/recharge', [RechargeController::class, 'recharge']);
    
    // 支付相关路由
    Route::get('/payment/methods', [PaymentController::class, 'getPaymentMethods']);
    Route::post('/payment/create', [PaymentController::class, 'createPayment']);
    
    // 分销商相关路由
    Route::prefix('distributor')->middleware('role:distributor')->group(function () {
        Route::get('/invited-users', [DistributorController::class, 'getInvitedUsers']);
        Route::get('/profile', [DistributorController::class, 'getProfile']);
    });
    
    // 管理员相关路由
    Route::prefix('admin')->middleware('role:admin')->group(function () {
        // 用户管理
        Route::get('/users', [AdminUserController::class, 'index']);
        Route::patch('/users/{id}/expire-time', [AdminUserController::class, 'updateExpireTime']);
        Route::patch('/users/{id}/password', [AdminUserController::class, 'updatePassword']);
        
        // 分销商管理
        Route::get('/distributors', [AdminDistributorController::class, 'index']);
        Route::post('/distributors', [AdminDistributorController::class, 'store']);
        Route::patch('/distributors/{id}', [AdminDistributorController::class, 'update']);
        Route::patch('/distributors/{id}/status', [AdminDistributorController::class, 'updateStatus']);
        Route::delete('/distributors/{id}', [AdminDistributorController::class, 'destroy']);
        Route::get('/distributors/search-users', [AdminDistributorController::class, 'searchUsers']);
        
        // 系统设置
        Route::get('/settings', [AdminSettingController::class, 'index']);
        Route::patch('/settings', [AdminSettingController::class, 'update']);
        
        // 支付设置
        Route::get('/payment/methods', [AdminPaymentController::class, 'getPaymentMethods']);
        Route::get('/payment/settings', [AdminPaymentController::class, 'getPaymentSettings']);
        Route::patch('/payment/methods/{id}/status', [AdminPaymentController::class, 'updatePaymentMethodStatus']);
        Route::patch('/payment/settings', [AdminPaymentController::class, 'updatePaymentSettings']);
        Route::patch('/payment/methods/orders', [AdminPaymentController::class, 'updatePaymentMethodOrders']);
    });
});
