<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\OrderController;


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

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::middleware('auth:api')->post('/logout', [AuthController::class, 'logout']);

Route::middleware('auth:api')->group(function () {

Route::post('product/store',[ProductController::class,'store']);
Route::get('product/index',[ProductController::class,'index']);
Route::get('product/show/{id}',[ProductController::class,'show']);
Route::put('product/update/{id}',[ProductController::class,'update']);
Route::delete('product/delete/{id}',[ProductController::class,'destroy']);


Route::post('/order/store', [OrderController::class, 'store']);
Route::get('/order/index', [OrderController::class, 'index']);
Route::get('/order/show/{id}',[OrderController::class,'show']);

Route::put('/order/status/{id}', [OrderController::class, 'updateStatus']);
Route::post('/order/capture-payment', [OrderController::class, 'capturePayment']); // Capture PayPal payment


});
