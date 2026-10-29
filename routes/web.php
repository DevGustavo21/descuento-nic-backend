<?php

use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\MediaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/negocio');
});

Route::get('/media/businesses/{business}/logo', [MediaController::class, 'businessLogo'])
    ->name('media.business-logo');

Route::post('/impersonation/stop', [ImpersonationController::class, 'stop'])
    ->middleware(['web', 'auth'])
    ->name('impersonation.stop');
