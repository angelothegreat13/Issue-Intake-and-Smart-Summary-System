<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('issues.index'));

Route::resource('issues', IssueController::class)->except(['destroy']);
