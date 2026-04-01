<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AkunController;

Route::get('/', fn() => redirect()->route('akun.index'));

// TC-12: Export / Import / Template
Route::get('akun/export',          [AkunController::class, 'export'])->name('akun.export');
Route::get('akun/template',        [AkunController::class, 'template'])->name('akun.template');
Route::post('akun/import',         [AkunController::class, 'import'])->name('akun.import');

// Detail (JSON) for modals
Route::get('akun/{kode}/detail',   [AkunController::class, 'detail'])->name('akun.detail');

// TC-01, TC-03, TC-06: COA
Route::resource('akun', AkunController::class)->only(['index', 'store', 'show', 'update']);
Route::patch('akun/{kode}/induk', [AkunController::class, 'updateInduk'])->name('akun.updateInduk');
Route::patch('akun/{kode}/toggle-aktif', [AkunController::class, 'toggleAktif'])->name('akun.toggleAktif');
