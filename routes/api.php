<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/all-user', [AuthController::class, 'allUser']);

// category
Route::get('/category', [CategoryController::class, 'index']);
Route::post('/category', [CategoryController::class, 'store']);
Route::get('/category/{id}/products', [CategoryController::class, 'getProductsByCategoryId']);
Route::get('/category/slug/{slug}/products', [CategoryController::class, 'getProductsByCategorySlug']);
Route::get('/category/search', [CategoryController::class, 'search']);


// product
Route::get('/product', [ProductController::class, 'index']);
Route::post('/product', [ProductController::class, 'store']);


Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/change-password',[AuthController::class,'changePassword']);

    Route::post('/cart',[CartController::class,'store']);
    Route::get('/cart',[CartController::class,'index']);
    Route::delete('/cart/{id}',[CartController::class,'destroy']);
    Route::put('/cart/{id}',[CartController::class,'update']);
});
