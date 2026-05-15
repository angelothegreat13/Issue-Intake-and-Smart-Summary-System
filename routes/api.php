<?php

use App\Http\Controllers\Api\IssueController;
use Illuminate\Support\Facades\Route;

Route::get('/issues',          [IssueController::class, 'index']);
Route::post('/issues',         [IssueController::class, 'store']);
Route::get('/issues/{issue}',  [IssueController::class, 'show']);
Route::patch('/issues/{issue}',[IssueController::class, 'update']);
