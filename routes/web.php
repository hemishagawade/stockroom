<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\MaterialController;
use App\Http\Controllers\StockMovementController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/materials');
Route::patch('materials/{material}/restore', [MaterialController::class, 'restore'])
    ->withTrashed()
    ->name('materials.restore');
Route::resource('materials', MaterialController::class)->except('show');
Route::resource('categories', CategoryController::class)->except('show');
Route::resource('stock-movements', StockMovementController::class)
    ->except('show')
    ->parameters(['stock-movements' => 'stockMovement']);
