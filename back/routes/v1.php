<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DealController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\FolioController;
use App\Http\Controllers\Api\MediaController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\ProjectController;
use App\Http\Controllers\Api\CustomerController;
use App\Http\Controllers\Api\ReapplyFolioController;
use App\Http\Controllers\Api\FolioActivityController;
use App\Http\Controllers\Api\AssignFolioCodeController;
use App\Http\Controllers\Api\AssignExistingFolioController;
use App\Http\Controllers\Api\ValidateBcSkuController;
use App\Http\Controllers\Api\ReturnToApprovedController;
use App\Http\Controllers\Api\UpdateFolioStateController;
use App\Http\Controllers\Api\AvgTimeTransitionReportController;
use App\Http\Controllers\Api\ApproveQuoteFoliosController;

Route::post('login', [AuthController::class, 'login'])->name('token.login');

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/auth', [AuthController::class, 'user']);

    Route::apiResource('users', UserController::class);
    Route::get('folios/validate-bc-sku', ValidateBcSkuController::class)->name('folios.validate-bc-sku');
    Route::apiResource('folios', FolioController::class);

    Route::post('folios/{folio}/state', UpdateFolioStateController::class)->name('folios.state.update');
    Route::get('folios/{folio}/activities', FolioActivityController::class)->name('folios.activities');
    Route::post('folios/{folio}/reapply', ReapplyFolioController::class)->name('folios.reapply');
    Route::post('folios/{folio}/return-to-approved', ReturnToApprovedController::class)->name('folios.return-to-approved');
    Route::post('folios/{folio}/assign-folio-code', AssignFolioCodeController::class)->name('folios.assign-folio-code');
    Route::post('folios/{folio}/assign-existing-folio', AssignExistingFolioController::class)->name('folios.assign-existing-folio');

    Route::get('products', [ProductController::class, 'index']);
    Route::get('products/{product}/measures', [ProductController::class, 'measures']);
    Route::get('categories', [CategoryController::class, 'index']);
    Route::get('customers', [CustomerController::class, 'index']);
    Route::get('deals', DealController::class);
    Route::get('deals/{deal}', [DealController::class, 'show']);
    Route::post('projects', [ProjectController::class, 'store'])->name('projects.store');

    Route::delete('media/{media}', [MediaController::class, 'destroy']);


    Route::group(['prefix' => 'reports'], function () {
        Route::get('avg-time-transition', AvgTimeTransitionReportController::class)->name('reports.avg-time-transition');
    });

});

Route::middleware('service.auth')->prefix('s2s')->group(function () {
    Route::post('projects', [ProjectController::class, 'store'])->name('s2s.projects.store');
    Route::post('folios/approve-by-quote', ApproveQuoteFoliosController::class)->name('s2s.folios.approve-by-quote');
});
