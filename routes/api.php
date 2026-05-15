<?php

use App\Http\Controllers\Api\V1\IssueApiController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::apiResource('issues', IssueApiController::class)->except(['destroy']);
});
