<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GameController;

Route::get('/', function () {
    return view('game');
});

Route::get('/{any}', function () {
    return view('game');
})->where('any', '.*');