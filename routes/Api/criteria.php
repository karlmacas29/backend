<?php

use App\Http\Controllers\CriteriaController;
use Illuminate\Support\Facades\Route;




Route::post('/store/criteria',[CriteriaController::class,'store_criteria']);
