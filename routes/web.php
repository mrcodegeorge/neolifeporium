<?php

use App\Http\Controllers\Web\AdminManagementController;
use App\Http\Controllers\Web\AdminSettingsController;
use App\Http\Controllers\Web\AdvisoryController;
use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\CartController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\DashboardDataController;
use App\Http\Controllers\Web\HomeController;
use App\Http\Controllers\Web\InstallController;
use App\Http\Controllers\Web\KnowledgeHubController;
use App\Http\Controllers\Web\MarketplaceController;
use App\Http\Controllers\Web\RoleController;
use App\Http\Controllers\Web\VendorProductController;
use Illuminate\Support\Facades\Route;

Route::middleware('not_installed')->prefix('install')->name('install.')->group(function () {
    Route::get('/', [InstallController::class, 'welcome'])->name('welcome');
    Route::get('/requirements', [InstallController::class, 'requirements'])->name('requirements');
    Route::get('/database', [InstallController::class, 'database'])->name('database');
    Route::post('/database', [InstallController::class, 'saveDatabase'])->name('database.save');
    Route::get('/migrate', [InstallController::class, 'migrate'])->name('migrate');
    Route::post('/migrate', [InstallController::class, 'runMigrate'])->name('migrate.run');
    Route::get('/admin', [InstallController::class, 'admin'])->name('admin');
    Route::post('/admin', [InstallController::class, 'saveAdmin'])->name('admin.save');
    Route::get('/config', [InstallController::class, 'config'])->name('config');
    Route::post('/config', [InstallController::class, 'saveConfig'])->name('config.save');
    Route::get('/finish', [InstallController::class, 'finish'])->name('finish');
    Route::post('/finish', [InstallController::class, 'runFinish'])->name('finish.run');
});
Route::get('/install/success', [InstallController::class, 'success'])->name('install.success');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.attempt');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/my-dashboard', [RoleController::class, 'redirectDashboard'])->name('dashboard.redirect');
    Route::get('/roles/choose', [RoleController::class, 'choose'])->name('roles.choose');
    Route::post('/roles/switch', [RoleController::class, 'switch'])->name('roles.switch');
    Route::get('/roles/onboarding', [RoleController::class, 'onboarding'])->name('roles.onboarding');
    Route::post('/roles/apply/vendor', [RoleController::class, 'applyVendor'])->name('roles.apply.vendor');
    Route::post('/roles/apply/expert', [RoleController::class, 'applyExpert'])->name('roles.apply.expert');
});

Route::any('/', [HomeController::class, 'index'])->name('home');
Route::get('/marketplace', [MarketplaceController::class, 'index'])->name('marketplace.index');
Route::get('/marketplace/{slug}', [MarketplaceController::class, 'show'])->name('marketplace.show');
Route::get('/knowledge-hub', [KnowledgeHubController::class, 'index'])->name('knowledge.index');
Route::get('/knowledge-hub/category/{slug}', [KnowledgeHubController::class, 'category'])->name('knowledge.category');
Route::get('/knowledge-hub/{slug}', [KnowledgeHubController::class, 'show'])->name('knowledge.show');
Route::get('/advisory', [AdvisoryController::class, 'index'])->name('advisory.index');
Route::get('/advisory/experts/{expertId}', [AdvisoryController::class, 'show'])->name('advisory.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/add', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/update', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/remove', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout.store');

Route::middleware(['auth', 'role:farmer,admin,super_admin'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'farmer'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardDataController::class, 'farmer'])->name('dashboard.data');
    Route::get('/dashboard/export/orders', [DashboardDataController::class, 'exportFarmerOrdersCsv'])->name('dashboard.export.orders');
    Route::post('/advisory/experts/{expertId}/book', [AdvisoryController::class, 'book'])->name('advisory.book');
    Route::get('/advisory/bookings/{booking}/chat', [AdvisoryController::class, 'chat'])->name('advisory.chat');
    Route::post('/advisory/bookings/{booking}/messages', [AdvisoryController::class, 'sendMessage'])->name('advisory.messages.send');
});

Route::middleware(['auth', 'role:vendor,admin,super_admin'])->group(function () {
    Route::get('/vendor-panel', [DashboardController::class, 'vendor'])->name('vendor.panel');
    Route::get('/vendor-panel/data', [DashboardDataController::class, 'vendor'])->name('vendor.data');
    Route::patch('/vendor-panel/orders/{order}/status', [DashboardDataController::class, 'vendorUpdateOrderStatus'])->name('vendor.orders.status');
    Route::get('/vendor-panel/export/orders', [DashboardDataController::class, 'exportVendorOrdersCsv'])->name('vendor.export.orders');
});

Route::middleware(['auth', 'role:agronomist,admin,super_admin'])->group(function () {
    Route::get('/expert-panel', [DashboardController::class, 'expert'])->name('expert.panel');
    Route::get('/expert-panel/data', [DashboardDataController::class, 'expert'])->name('expert.data');
    Route::patch('/expert-panel/bookings/{booking}/status', [DashboardDataController::class, 'expertUpdateBookingStatus'])->name('expert.bookings.status');
    Route::patch('/expert-panel/availability', [DashboardDataController::class, 'toggleExpertAvailability'])->name('expert.availability.toggle');
});

Route::middleware(['auth', 'role:admin,super_admin'])->group(function () {
    Route::get('/admin-panel', [DashboardController::class, 'admin'])->name('admin.panel');
    Route::get('/admin-panel/data', [DashboardDataController::class, 'admin'])->name('admin.data');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard/notifications', [DashboardDataController::class, 'notifications'])->name('dashboard.notifications');
    Route::post('/dashboard/notifications/{notification}/read', [DashboardDataController::class, 'markNotificationRead'])->name('dashboard.notifications.read');
    Route::get('/dashboard/search', [DashboardDataController::class, 'search'])->name('dashboard.search');
});

Route::middleware(['auth', 'role:vendor,admin,super_admin'])->prefix('/vendor-panel/products')->name('vendor.products.')->group(function () {
    Route::get('/', [VendorProductController::class, 'index'])->name('index');
    Route::get('/create', [VendorProductController::class, 'create'])->name('create');
    Route::post('/', [VendorProductController::class, 'store'])->name('store');
    Route::get('/{product}/edit', [VendorProductController::class, 'edit'])->name('edit');
    Route::match(['put', 'patch'], '/{product}', [VendorProductController::class, 'update'])->name('update');
    Route::delete('/{product}', [VendorProductController::class, 'destroy'])->name('destroy');
});

Route::middleware(['auth', 'role:admin,super_admin', 'audit.admin'])->prefix('/admin-panel')->name('admin.')->group(function () {
    Route::get('/export/orders', [AdminManagementController::class, 'exportOrdersCsv'])->name('export.orders');
    Route::get('/settings', [AdminSettingsController::class, 'index'])->name('settings');
    Route::patch('/settings', [AdminSettingsController::class, 'updatePlatformSettings'])->name('settings.update');
    Route::post('/staff', [AdminSettingsController::class, 'storeStaff'])->name('staff.store');
    Route::patch('/users/{user}/roles', [AdminSettingsController::class, 'updateUserRoles'])->name('users.roles');
    Route::get('/users', [AdminManagementController::class, 'users'])->name('users');
    Route::get('/vendors', [AdminManagementController::class, 'vendors'])->name('vendors');
    Route::get('/experts', [AdminManagementController::class, 'experts'])->name('experts');
    Route::get('/products', [AdminManagementController::class, 'products'])->name('products');
    Route::patch('/users/{user}/status', [AdminManagementController::class, 'updateUserStatus'])->name('users.status');
    Route::patch('/vendors/{vendorProfile}/status', [AdminManagementController::class, 'updateVendorStatus'])->name('vendors.status');
    Route::patch('/experts/{agronomistProfile}/status', [AdminManagementController::class, 'updateExpertStatus'])->name('experts.status');
    Route::patch('/products/{product}/moderate', [AdminManagementController::class, 'moderateProduct'])->name('products.moderate');
    Route::patch('/products/{product}/feature', [AdminManagementController::class, 'toggleFeaturedProduct'])->name('products.feature');
    Route::patch('/payments/{payment}/verify', [AdminManagementController::class, 'markPaymentVerified'])->name('payments.verify');
    Route::patch('/bookings/{booking}/status', [AdminManagementController::class, 'updateBookingStatus'])->name('bookings.status');
});
