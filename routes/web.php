<?php

use Illuminate\Support\Facades\Route;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

Route::get('/test-paypal', function () {
    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));
    $token = $provider->getAccessToken();

    return response()->json($token);
});

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
