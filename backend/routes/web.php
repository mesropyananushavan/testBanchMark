<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['auth', 'permission:dashboard.view'])->get('/dashboard', function () {
    return view('welcome');
})->name('dashboard');

Route::middleware(['auth', 'can:manage-users'])->get('/admin/users', function () {
    return response()->json([
        'ok' => true,
        'message' => 'Authorized to manage users.',
    ]);
})->name('admin.users.index');
