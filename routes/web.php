<?php

use Illuminate\Support\Facades\Route;
use Revo\MarketingTool\Http\Controllers\MarketingController;

Route::get('/', [MarketingController::class, 'index'])->name('marketing-tool.index');
Route::get('/data', [MarketingController::class, 'data'])->name('marketing-tool.data');
