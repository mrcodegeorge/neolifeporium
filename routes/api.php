<?php

use App\Http\Controllers\Api\Admin\AdminController;
use App\Http\Controllers\Api\Advisory\BookingController;
use App\Http\Controllers\Api\Advisory\BookingMessageController;
use App\Http\Controllers\Api\Advisory\ExpertController;
use App\Http\Controllers\Api\Advisory\ExpertReviewController;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\Auth\RoleController as AuthRoleController;
use App\Http\Controllers\Api\Content\ArticleController;
use App\Http\Controllers\Api\Content\CategoryController;
use App\Http\Controllers\Api\Marketplace\CartController;
use App\Http\Controllers\Api\Marketplace\OrderController;
use App\Http\Controllers\Api\Marketplace\ProductController;
use App\Http\Controllers\Api\Payments\PaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->middleware('throttle:api')->group(function () {
    Route::post('/auth/register', [AuthController::class, 'register']);
    Route::post('/auth/login', [AuthController::class, 'login']);
    Route::post('/payments/webhook', [PaymentController::class, 'webhook'])->name('payments.webhook');

    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);
    Route::get('/experts', [ExpertController::class, 'index']);
    Route::get('/experts/{id}', [ExpertController::class, 'show']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/auth/logout', [AuthController::class, 'logout']);
        Route::get('/me', fn (Request $request) => $request->user()->load('roles', 'farmerProfile', 'vendorProfile', 'agronomistProfile'));

        Route::middleware('role:vendor,admin,super_admin')->group(function () {
            Route::post('/products', [ProductController::class, 'store']);
            Route::match(['put', 'patch'], '/products/{product}', [ProductController::class, 'update']);
            Route::delete('/products/{product}', [ProductController::class, 'destroy']);
            Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus']);
        });

        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/farmers/{farmerId}/orders', [OrderController::class, 'history']);
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::patch('/cart/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/{itemId}', [CartController::class, 'destroy']);

        Route::post('/payments/initiate', [PaymentController::class, 'initiate']);
        Route::post('/payments/{payment}/verify', [PaymentController::class, 'verify']);

        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{bookingId}/messages', [BookingMessageController::class, 'index']);
        Route::post('/messages', [BookingMessageController::class, 'store']);
        Route::post('/experts/{id}/reviews', [ExpertReviewController::class, 'store']);
        Route::get('/articles/recommended/me', [ArticleController::class, 'recommended']);

        Route::middleware('role:admin,super_admin')->group(function () {
            Route::get('/admin/dashboard', [AdminController::class, 'dashboard']);
            Route::get('/admin/users', [AdminController::class, 'users']);
            Route::get('/admin/orders', [AdminController::class, 'orders']);
            Route::get('/admin/payments', [AdminController::class, 'payments']);
            Route::patch('/admin/vendors/{vendorProfile}/approval', [AdminController::class, 'approveVendor']);
            Route::patch('/admin/experts/{agronomistProfile}/approval', [AdminController::class, 'approveExpert']);
            Route::patch('/admin/products/{product}/moderate', [AdminController::class, 'moderateProduct']);
        });

        Route::post('/roles/assign', [AuthRoleController::class, 'assign']);
        Route::get('/user/roles', [AuthRoleController::class, 'roles']);
        Route::post('/vendor/apply', [AuthRoleController::class, 'applyVendor']);
        Route::get('/vendor/status', [AuthRoleController::class, 'vendorStatus']);
        Route::post('/expert/apply', [AuthRoleController::class, 'applyExpert']);
        Route::get('/expert/status', [AuthRoleController::class, 'expertStatus']);
    });
});

Route::middleware('throttle:api')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/experts', [ExpertController::class, 'index']);
    Route::get('/experts/{id}', [ExpertController::class, 'show']);
    Route::get('/articles', [ArticleController::class, 'index']);
    Route::get('/articles/{slug}', [ArticleController::class, 'show']);
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{slug}', [ProductController::class, 'show']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/roles/assign', [AuthRoleController::class, 'assign']);
        Route::get('/user/roles', [AuthRoleController::class, 'roles']);
        Route::post('/vendor/apply', [AuthRoleController::class, 'applyVendor']);
        Route::get('/vendor/status', [AuthRoleController::class, 'vendorStatus']);
        Route::post('/expert/apply', [AuthRoleController::class, 'applyExpert']);
        Route::get('/expert/status', [AuthRoleController::class, 'expertStatus']);
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/bookings', [BookingController::class, 'store']);
        Route::get('/bookings/{bookingId}/messages', [BookingMessageController::class, 'index']);
        Route::post('/messages', [BookingMessageController::class, 'store']);
        Route::post('/experts/{id}/reviews', [ExpertReviewController::class, 'store']);
        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::patch('/cart/{itemId}', [CartController::class, 'update']);
        Route::delete('/cart/{itemId}', [CartController::class, 'destroy']);
        Route::post('/orders', [OrderController::class, 'store']);
    });
});
