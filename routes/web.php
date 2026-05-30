<?php

use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReceiptController;
use App\Http\Controllers\ReportController;
use App\Livewire\Categories\Index as CategoriesIndex;
use App\Livewire\Dashboard;
use App\Livewire\Ingredients\Index as IngredientsIndex;
use App\Livewire\Menu;
use App\Livewire\Orders\Index as OrdersIndex;
use App\Livewire\Pos;
use App\Livewire\Products\Index as ProductsIndex;
use App\Livewire\Reports\Index as ReportsIndex;
use App\Livewire\Transactions\Index as TransactionsIndex;
use App\Livewire\Users\Index as UsersIndex;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Halaman publik: menu & pemesanan pelanggan (tanpa login)
Route::get('menu', Menu::class)->name('menu');

// Webhook notifikasi Midtrans (server-to-server, tanpa CSRF — lihat bootstrap/app.php)
Route::post('midtrans/notification', [PaymentController::class, 'notify'])->name('midtrans.notify');

// Beranda: staff yang sudah login -> dashboard, selain itu -> menu publik
Route::get('/', fn () => auth()->check()
    ? redirect()->route('dashboard')
    : redirect()->route('menu'));

Route::middleware(['auth'])->group(function () {
    Route::get('dashboard', Dashboard::class)->name('dashboard');
    Route::view('profile', 'profile')->name('profile');

    // Logout (POST)
    Route::post('logout', function () {
        Auth::guard('web')->logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/login');
    })->name('logout');

    // POS & Transaksi — admin & kasir
    Route::middleware('role:admin|kasir')->group(function () {
        Route::get('pos', Pos::class)->name('pos');
        Route::get('transactions', TransactionsIndex::class)->name('transactions.index');
        Route::get('transactions/{transaction}/receipt', [ReceiptController::class, 'show'])->name('transactions.receipt');
        Route::get('transactions/{transaction}/receipt/pdf', [ReceiptController::class, 'pdf'])->name('transactions.receipt.pdf');
    });

    // Manajemen pesanan — admin & barista
    Route::middleware('role:admin|barista')->group(function () {
        Route::get('orders', OrdersIndex::class)->name('orders.index');
    });

    // Khusus admin
    Route::middleware('role:admin')->group(function () {
        Route::get('categories', CategoriesIndex::class)->name('categories.index');
        Route::get('products', ProductsIndex::class)->name('products.index');
        Route::get('ingredients', IngredientsIndex::class)->name('ingredients.index');
        Route::get('users', UsersIndex::class)->name('users.index');

        Route::get('reports', ReportsIndex::class)->name('reports.index');
        Route::get('reports/export/csv', [ReportController::class, 'csv'])->name('reports.export.csv');
        Route::get('reports/export/pdf', [ReportController::class, 'pdf'])->name('reports.export.pdf');
    });
});

require __DIR__.'/auth.php';
