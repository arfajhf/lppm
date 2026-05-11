<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\AccessController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\SSOAuthController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', [HomeController::class, 'welcome'])->name('welcome');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::get('/auth/sso/redirect', [SSOAuthController::class, 'redirectToSSO'])->name('sso.redirect');
    Route::get('/auth/sso/callback', [SSOAuthController::class, 'handleCallback'])->name('sso.callback');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
    Route::get('/logout', [AuthController::class, 'destroy'])->name('logout.get');

    Route::get('/dashboard', [HomeController::class, 'dashboard'])
        ->middleware('lppm.permission:lppm.dashboard.view')
        ->name('dashboard');
    Route::get('/home', fn () => redirect()->route('dashboard'))->name('home');

    Route::get('/proposals', [ProposalController::class, 'index'])
        ->middleware('lppm.permission:lppm.proposal.view')
        ->name('proposals.index');
    Route::get('/proposals/create', [ProposalController::class, 'create'])
        ->middleware('lppm.permission:lppm.proposal.create')
        ->name('proposals.create');
    Route::post('/proposals', [ProposalController::class, 'store'])
        ->middleware('lppm.permission:lppm.proposal.create')
        ->name('proposals.store');
    Route::get('/proposals/{proposal:proposal_id}', [ProposalController::class, 'show'])
        ->middleware('lppm.permission:lppm.proposal.view')
        ->name('proposals.show');
    Route::post('/proposals/{proposal:proposal_id}/advance', [ProposalController::class, 'advance'])
        ->middleware('lppm.permission:lppm.proposal.view')
        ->name('proposals.advance');

    Route::get('/documents', [DocumentController::class, 'index'])
        ->middleware('lppm.permission:lppm.document.view')
        ->name('documents.index');
    Route::get('/reviews', [ReviewController::class, 'index'])
        ->middleware('lppm.permission:lppm.review.view')
        ->name('reviews.index');
    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('lppm.permission:lppm.report.view')
        ->name('reports.index');
    Route::get('/notifications', [NotificationController::class, 'index'])
        ->middleware('lppm.permission:lppm.notification.view')
        ->name('notifications.index');
    Route::get('/access-matrix', [AccessController::class, 'index'])
        ->middleware('lppm.permission:lppm.access.manage')
        ->name('access.index');
});
