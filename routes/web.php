<?php

use Illuminate\Support\Facades\Route;

require __DIR__.'/auth.php';

Route::redirect('/', '/login');  

Route::middleware('auth')->group(function () {
    Route::view('/', 'repair.index')->name('repair.index');        // หน้าแรกเป็นหน้าระบบ
});
