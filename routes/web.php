<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\AdminController;

Route::get('/', [ProductController::class, 'index']);
Route::get('/products/{product_id}', [ProductController::class, 'show'])->name('products.show');

Route::name('login')
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/login', 'loginPage');
        Route::post('/login', 'login')->name('.submit');
    });

Route::middleware(['auth'])->group(function () {
    Route::get('/logout', [AdminController::class, 'logout'])->name('logout');
});

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth'])
    ->controller(AdminController::class)
    ->group(function () {
        Route::get('/products', 'products')->name('products');
        Route::get('/products/add', 'addProductForm')->name('add.product');
        Route::post('/products/add', 'addProduct')->name('add.product.submit');
        Route::get('/products/edit/{id}', 'editProduct')->name('edit.product');
        Route::post('/products/edit/{id}', 'updateProduct')->name('update.product');
        Route::get('/products/delete/{id}', 'deleteProduct')->name('delete.product');
    });
