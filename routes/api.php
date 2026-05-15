<?php

use App\Http\Controllers\Api\V1\IssueController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->name('api.v1.')->group(function () {
    Route::apiResource('issues', IssueController::class)->except(['destroy']);
});
