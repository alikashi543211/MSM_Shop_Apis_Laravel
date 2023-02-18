<?php

use App\Http\Controllers\Api\PdfController;
use App\Http\Controllers\Api\QuickBookController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return redirect()->to('https://dashboard.mailboxes.bm');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth'])->name('dashboard');

// Pdf Create and Download Route
Route::get('download-pdf', [PdfController::class, 'testDownloadPdf']);
Route::get('quickbook/connect', [QuickBookController::class, 'connect']);
Route::get('quickbook/callback', [QuickBookController::class, 'callback']);
Route::get('quickbook/make-api-call', [QuickBookController::class, 'makeApiCall']);
Route::get('quickbook/create-customer', [QuickBookController::class, 'createCustomer']);

require __DIR__.'/auth.php';
