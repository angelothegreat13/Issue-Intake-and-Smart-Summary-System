<?php

use App\Http\Controllers\IssueController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('issues.index'));

Route::get('/issues',             [IssueController::class, 'index'])->name('issues.index');
Route::get('/issues/create',      [IssueController::class, 'create'])->name('issues.create');
Route::post('/issues',            [IssueController::class, 'store'])->name('issues.store');
Route::get('/issues/{issue}',     [IssueController::class, 'show'])->name('issues.show');
Route::get('/issues/{issue}/edit',[IssueController::class, 'edit'])->name('issues.edit');
Route::patch('/issues/{issue}',   [IssueController::class, 'update'])->name('issues.update');
