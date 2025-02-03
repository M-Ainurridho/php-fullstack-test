<?php

use App\Http\Controllers\MyClientController;
use Illuminate\Support\Facades\Route;

Route::apiResource('clients', MyClientController::class);