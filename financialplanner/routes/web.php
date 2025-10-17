<?php

use App\Http\Controllers\BudgetController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\WalletController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/transactions/export-csv', [TransactionController::class, 'exportCsv'])->name('transactions.export');
Route::resource('transactions', TransactionController::class)->except(['show']);

Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

Route::get('/wallets', [WalletController::class, 'index'])->name('wallets.index');
Route::post('/wallets', [WalletController::class, 'store'])->name('wallets.store');
Route::put('/wallets/{wallet}', [WalletController::class, 'update'])->name('wallets.update');
Route::delete('/wallets/{wallet}', [WalletController::class, 'destroy'])->name('wallets.destroy');

Route::get('/budgets', [BudgetController::class, 'index'])->name('budgets.index');
Route::post('/budgets', [BudgetController::class, 'store'])->name('budgets.store');
Route::put('/budgets/{budget}', [BudgetController::class, 'update'])->name('budgets.update');
Route::delete('/budgets/{budget}', [BudgetController::class, 'destroy'])->name('budgets.destroy');

Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
