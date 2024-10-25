<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Presensi;
use Illuminate\Support\Facades\Artisan;

Route::group(['middleware' => 'auth'], function () {
    Route::get('presensi', Presensi::class)->name('presensi');
});

Route::get('/login', function () {
    return redirect('admin/login');
})->name('login');

Route::get('/', function () {
    return redirect('admin/login');
});

Route::get('/hello', function () {
    return 'Hello World!';
});
