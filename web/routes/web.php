<?php

use App\Http\Controllers\Web\Auth\LoginController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\AppointmentController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('login'));

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);

    // Password Reset Routes
    Route::get('/password/reset', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'showEmailForm'])->name('password.request');
    Route::post('/password/email', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'sendOtp'])->name('password.email')->middleware('throttle:3,1');
    Route::get('/password/verify', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'showVerifyForm'])->name('password.verify');
    Route::post('/password/verify', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'verifyOtp']);
    Route::get('/password/new', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'showResetForm'])->name('password.reset');
    Route::post('/password/update', [\App\Http\Controllers\Web\Auth\ForgotPasswordController::class, 'resetPassword'])->name('password.update');
});

Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    // -------------------------------------------------------------------------
    // KATMAN 1: Tüm yetkili personel (barber dahil)
    // -------------------------------------------------------------------------
    Route::middleware(['role:super_admin,owner,manager,receptionist,barber'])->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/dashboard/appointment-stats', [DashboardController::class, 'appointmentStats'])->name('dashboard.appointment-stats');
        Route::get('/dashboard/top-services', [DashboardController::class, 'topServices'])->name('dashboard.top-services');

        Route::get('/appointments/events', [AppointmentController::class, 'events'])->name('appointments.events');
        Route::get('/appointments/available-slots', [AppointmentController::class, 'availableSlots'])->name('appointments.available-slots');
        Route::resource('appointments', AppointmentController::class)->except(['destroy']);
        Route::patch('/appointments/{appointment}/status', [AppointmentController::class, 'updateStatus'])->name('appointments.update-status');
        Route::post('/appointments/{appointment}/payments', [AppointmentController::class, 'storePayment'])->name('appointments.payments.store');
        Route::delete('/appointments/{appointment}/payments/{payment}', [AppointmentController::class, 'destroyPayment'])->name('appointments.payments.destroy');
        Route::post('/appointments/{appointment}/complete-payment', [AppointmentController::class, 'completeWithPayment'])->name('appointments.complete-payment');

        // Sistem Bildirimleri: her personel yalnızca kendi bildirimlerini yönetir (Policy ile korunur).
        // Bildirim oluşturma uç noktası bilinçli olarak yoktur; kayıtlar Observer'lar tarafından üretilir.
        Route::controller(App\Http\Controllers\Web\NotificationController::class)
            ->prefix('notifications')
            ->name('notifications.')
            ->group(function () {
                Route::get('/', 'index')->name('index');
                Route::get('/feed', 'feed')->name('feed')->middleware('throttle:60,1');
                Route::post('/mark-all-read', 'markAllRead')->name('mark-all-read');
                Route::delete('/read', 'destroyRead')->name('destroy-read');
                Route::get('/{notification}/open', 'open')->name('open')->whereNumber('notification');
                Route::patch('/{notification}/toggle-read', 'toggleRead')->name('toggle-read')->whereNumber('notification');
                Route::delete('/{notification}', 'destroy')->name('destroy')->whereNumber('notification');
            });
    });

    // -------------------------------------------------------------------------
    // KATMAN 2: Yönetim + Resepsiyon (barber hariç)
    // -------------------------------------------------------------------------
    Route::middleware(['role:super_admin,owner,manager,receptionist'])->group(function () {
        Route::resource('customers', App\Http\Controllers\Web\CustomerController::class);
        Route::get('/customers/{customer}/loyalty', [App\Http\Controllers\Web\LoyaltyController::class, 'show'])->name('customers.loyalty.show');
        Route::post('/customers/{customer}/loyalty', [App\Http\Controllers\Web\LoyaltyController::class, 'store'])->name('customers.loyalty.store');
    });

    // -------------------------------------------------------------------------
    // KATMAN 3: Sadece Yönetim (super_admin, owner, manager)
    // -------------------------------------------------------------------------
    Route::middleware(['role:super_admin,owner,manager'])->group(function () {
        Route::resource('employees', App\Http\Controllers\Web\EmployeeController::class);
        Route::post('employee-time-blocks/quick', [App\Http\Controllers\Web\EmployeeTimeBlockController::class, 'quickStore'])->name('employee-time-blocks.quick-store');
        Route::resource('employee-time-blocks', App\Http\Controllers\Web\EmployeeTimeBlockController::class)->only(['index', 'store', 'update', 'destroy']);
        Route::apiResource('employee-titles', App\Http\Controllers\Web\EmployeeTitleController::class)->except(['show']);
        Route::resource('services', App\Http\Controllers\Web\ServiceController::class);
        Route::patch('/services/{service}/toggle-status', [App\Http\Controllers\Web\ServiceController::class, 'toggleStatus'])->name('services.toggle-status');

        // Finance
        Route::get('/finance/transactions', [App\Http\Controllers\Web\TransactionController::class, 'index'])->name('finance.transactions');
        Route::get('/finance/transactions/{transaction}', [App\Http\Controllers\Web\TransactionController::class, 'show'])->name('finance.transactions.show');
        Route::post('/finance/transactions', [App\Http\Controllers\Web\TransactionController::class, 'store'])->name('finance.transactions.store');
        Route::delete('/finance/transactions/{transaction}', [App\Http\Controllers\Web\TransactionController::class, 'destroy'])->name('finance.transactions.destroy');

        Route::get('/finance/receivables', [App\Http\Controllers\Web\DebtController::class, 'receivables'])->name('finance.receivables.index');
        Route::get('/finance/payables', [App\Http\Controllers\Web\DebtController::class, 'payables'])->name('finance.payables.index');
        Route::get('/finance/debts/details', [App\Http\Controllers\Web\DebtController::class, 'details'])->name('finance.debts.details');
        Route::post('/finance/debts', [App\Http\Controllers\Web\DebtController::class, 'store'])->name('finance.debts.store');
        Route::post('/finance/debts/{debt}/pay', [App\Http\Controllers\Web\DebtController::class, 'pay'])->name('finance.debts.pay');
        Route::delete('/finance/debts/{debt}', [App\Http\Controllers\Web\DebtController::class, 'destroy'])->name('finance.debts.destroy');

        Route::get('/finance/expenses', [App\Http\Controllers\Web\ExpenseController::class, 'index'])->name('finance.expenses');
        Route::post('/finance/expenses', [App\Http\Controllers\Web\ExpenseController::class, 'store'])->name('finance.expenses.store');
        Route::delete('/finance/expenses/{expense}', [App\Http\Controllers\Web\ExpenseController::class, 'destroy'])->name('finance.expenses.destroy');
        Route::post('/finance/expenses/categories', [App\Http\Controllers\Web\ExpenseController::class, 'storeCategory'])->name('finance.expenses.categories.store');

        Route::get('/finance/commissions', [App\Http\Controllers\Web\CommissionController::class, 'index'])->name('finance.commissions.index');

        // Products & Stock
        Route::resource('product-categories', App\Http\Controllers\Web\ProductCategoryController::class)->except(['show']);
        Route::resource('products', App\Http\Controllers\Web\ProductController::class);
        Route::patch('/products/{product}/toggle-status', [App\Http\Controllers\Web\ProductController::class, 'toggleStatus'])->name('products.toggle-status');
        Route::get('/products-sales', [App\Http\Controllers\Web\ProductSaleController::class, 'index'])->name('products.sales.index');
        Route::post('/products-sales', [App\Http\Controllers\Web\ProductSaleController::class, 'store'])->name('products.sales.store');
        Route::get('/products-sales/{productSale}', [App\Http\Controllers\Web\ProductSaleController::class, 'show'])->name('products.sales.show');

        // Cafe Management (New Module)
        Route::resource('cafe-categories', \App\Http\Controllers\Web\CafeCategoryController::class)->parameters([
            'cafe-categories' => 'cafeCategory'
        ]);
        Route::get('/cafe', [App\Http\Controllers\Web\CafeProductController::class, 'index'])->name('cafe.index');
        Route::post('/cafe', [App\Http\Controllers\Web\CafeProductController::class, 'store'])->name('cafe.store');
        Route::put('/cafe/{cafeProduct}', [App\Http\Controllers\Web\CafeProductController::class, 'update'])->name('cafe.update');
        Route::delete('/cafe/{cafeProduct}', [App\Http\Controllers\Web\CafeProductController::class, 'destroy'])->name('cafe.destroy');
        Route::patch('/cafe/{cafeProduct}/toggle-status', [App\Http\Controllers\Web\CafeProductController::class, 'toggleStatus'])->name('cafe.toggle-status');

        // Stock Management (New Modules)
        Route::resource('suppliers', App\Http\Controllers\Web\SupplierController::class)->except(['create', 'edit', 'show']);
        Route::get('/stock-movements', [App\Http\Controllers\Web\StockMovementController::class, 'index'])->name('stock-movements.index');
        Route::get('/stock-movements/{stockMovement}', [App\Http\Controllers\Web\StockMovementController::class, 'show'])->name('stock-movements.show');
        Route::resource('purchase-orders', App\Http\Controllers\Web\PurchaseOrderController::class)->except(['edit', 'update', 'destroy']);
        Route::resource('stock-counts', App\Http\Controllers\Web\StockCountController::class)->except(['edit', 'update', 'destroy']);

        // Campaigns
        Route::get('/campaigns', [App\Http\Controllers\Web\CampaignController::class, 'index'])->name('campaigns.index');
        Route::post('/campaigns', [App\Http\Controllers\Web\CampaignController::class, 'store'])->name('campaigns.store');
        Route::put('/campaigns/{campaign}', [App\Http\Controllers\Web\CampaignController::class, 'update'])->name('campaigns.update');
        Route::patch('/campaigns/{campaign}/toggle', [App\Http\Controllers\Web\CampaignController::class, 'toggleStatus'])->name('campaigns.toggle');
        Route::delete('/campaigns/{campaign}', [App\Http\Controllers\Web\CampaignController::class, 'destroy'])->name('campaigns.destroy');
        Route::get('/campaigns/{campaign}/usages', [App\Http\Controllers\Web\CampaignController::class, 'usages'])->name('campaigns.usages');
        Route::post('/campaigns/coupons', [App\Http\Controllers\Web\CampaignController::class, 'storeCoupon'])->name('campaigns.coupons.store');
        Route::put('/campaigns/coupons/{coupon}', [App\Http\Controllers\Web\CampaignController::class, 'updateCoupon'])->name('campaigns.coupons.update');
        Route::delete('/campaigns/coupons/{coupon}', [App\Http\Controllers\Web\CampaignController::class, 'destroyCoupon'])->name('campaigns.coupons.destroy');
        Route::get('/campaigns/coupons/{coupon}/usages', [App\Http\Controllers\Web\CampaignController::class, 'couponUsages'])->name('campaigns.coupons.usages');

        // Reviews
        Route::get('/reviews', [App\Http\Controllers\Web\ReviewController::class, 'index'])->name('reviews.index');
        Route::delete('/reviews/{review}', [App\Http\Controllers\Web\ReviewController::class, 'destroy'])->name('reviews.destroy');

        // Reports
        Route::get('/reports', [App\Http\Controllers\Web\ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/{type}', [App\Http\Controllers\Web\ReportController::class, 'show'])->name('reports.show');

        // Settings
        Route::get('/settings', [App\Http\Controllers\Web\SettingController::class, 'index'])->name('settings.index');
        Route::post('/settings/global', [App\Http\Controllers\Web\SettingController::class, 'updateGlobal'])->name('settings.global.update');
        Route::post('/settings/branch', [App\Http\Controllers\Web\SettingController::class, 'updateBranch'])->name('settings.branch.update');
    });
});
