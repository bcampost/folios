<?php

use App\Models\Folio;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PdfController;
use Illuminate\Support\Facades\Artisan;
use App\States\Folio\PrevioAprobadoState;
use App\States\Folio\PrevioSolicitadoState;
use App\Http\Controllers\FolioExportController;
use App\Http\Controllers\Api\AvgTimeTransitionReportController;
use App\Http\Controllers\Auth\SsoHandshakeController;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/storage-link', function () {
    Artisan::call('storage:link');
});

Route::get('/pdf/{folio}', [PdfController::class,'index']);
Route::get('/pdf/verData/{folio}', [PdfController::class,'verData']);
Route::get('/report', AvgTimeTransitionReportController::class);
Route::get('/folios/export', [FolioExportController::class, 'export']);
// ->middleware('auth:sanctum');

Route::get('/auth/sso/handshake', SsoHandshakeController::class);
