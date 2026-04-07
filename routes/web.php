<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AkunController;
use App\Http\Controllers\MutasiBankController;

Route::get('/', fn() => redirect()->route('akun.index'));

// TAF-06: Bank Mutation Import
Route::get('mutasi-bank/list',          [MutasiBankController::class, 'batchList'])->name('mutasi-bank.list');
Route::post('mutasi-bank/upload',       [MutasiBankController::class, 'upload'])->name('mutasi-bank.upload');
Route::get('mutasi-bank/{id}/data',     [MutasiBankController::class, 'dataMutasi'])->name('mutasi-bank.data');
Route::get('mutasi-bank/{id}/file',     [MutasiBankController::class, 'downloadFile'])->name('mutasi-bank.download-file');
Route::resource('mutasi-bank', MutasiBankController::class)->only(['index', 'create', 'show', 'store', 'destroy']);

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
