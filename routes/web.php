<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Presensi;
use Illuminate\Support\Facades\Artisan;

// Storage Link
Route::get('/storage-link', function () {
    Artisan::call('storage:link');
    return 'Storage linked successfully.';
});

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
