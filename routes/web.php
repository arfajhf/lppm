<?php

use App\Http\Controllers\AccessController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\GrantController;
use App\Http\Controllers\GrantSopController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LocalAuthenticationController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProposalController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ReviewerAppointmentController;
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
    Route::post('/auth/local/login', [LocalAuthenticationController::class, 'store'])->middleware('throttle:local-login')->name('auth.local.login');
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

Route::middleware('auth')->group(function () {
    Route::get('/grants', [GrantController::class, 'index'])->middleware('lppm.permission:lppm.proposal.view')->name('grants.index');
    Route::get('/grants/calls/{call}/announcement', [GrantController::class, 'announcement'])->name('grants.announcement');
    Route::post('/grants/calls', [GrantController::class, 'storeCall'])->name('grants.calls.store');
    Route::get('/grants/{proposal:proposal_id}', [GrantController::class, 'show'])->name('grants.show');
    Route::post('/grants/{proposal:proposal_id}/enroll', [GrantController::class, 'enroll'])->name('grants.enroll');
    Route::post('/grants/{proposal:proposal_id}/advance', [GrantController::class, 'advance'])->name('grants.advance');
    Route::post('/grants/{proposal:proposal_id}/documents', [GrantController::class, 'upload'])->name('grants.documents');
    Route::post('/grants/{proposal:proposal_id}/records', [GrantController::class, 'record'])->name('grants.records');
    Route::get('/documents/{document:document_id}/download', [GrantController::class, 'download'])->name('documents.download');
    Route::get('/proposals/{proposal:proposal_id}/edit', [ProposalController::class, 'edit'])->name('proposals.edit');
    Route::put('/proposals/{proposal:proposal_id}', [ProposalController::class, 'update'])->name('proposals.update');
});

Route::middleware(['auth', 'lppm.permission:lppm.proposal.view'])->group(function () {
    Route::get('/reviewer-appointments', [ReviewerAppointmentController::class, 'index'])->name('reviewer-appointments.index');
    Route::post('/reviewer-appointments', [ReviewerAppointmentController::class, 'store'])->name('reviewer-appointments.store');
    Route::post('/reviewer-appointments/{appointment}/advance', [ReviewerAppointmentController::class, 'advance'])->name('reviewer-appointments.advance');
    Route::get('/reviewer-appointments/{appointment}/files/{kind}', [ReviewerAppointmentController::class, 'download'])->name('reviewer-appointments.download');
});

Route::get('/announcements', [GrantController::class, 'announcements'])->name('grants.announcements');
Route::get('/announcements/{call}/document', [GrantController::class, 'announcement'])->name('grants.public-announcement');
Route::post('/reviewer-appointments/{appointment}/publish', [ReviewerAppointmentController::class, 'publish'])->middleware(['auth', 'lppm.permission:lppm.proposal.view'])->name('reviewer-appointments.publish');

Route::middleware(['auth', 'lppm.permission:lppm.proposal.view'])->group(function () {
    Route::get('/grant-sop/{scope}/{id}', [GrantSopController::class, 'show'])->name('grant-sop.show');
    Route::post('/grant-sop/{scope}/{id}/{step}', [GrantSopController::class, 'store'])->name('grant-sop.store');
    Route::get('/grant-sop-files/{action}/{kind}', [GrantSopController::class, 'download'])->name('grant-sop.download');
    Route::post('/grants/calls/{call}/publish', [GrantSopController::class, 'publishCall'])->name('grants.calls.publish');
    Route::get('/grants/calls/{call}/export', [GrantSopController::class, 'export'])->name('grants.calls.export');
});

Route::get('/announcements/reviewers/{appointment}/{kind}', [GrantController::class, 'reviewerAnnouncement'])->name('grants.reviewer-announcement');

Route::get('/grant-sop-receipts/{action}', [GrantSopController::class, 'receipt'])->middleware(['auth', 'lppm.permission:lppm.proposal.view'])->name('grant-sop.receipt');
