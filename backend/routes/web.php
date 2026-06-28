<?php

use App\Http\Controllers\Api\RedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/r/{slug}', [RedirectController::class, 'shortLink']);
Route::get('/qr/{code}', [RedirectController::class, 'qrCode']);
