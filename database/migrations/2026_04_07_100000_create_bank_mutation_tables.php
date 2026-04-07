<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('finance_ref_bank', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('kode_internal', 20)->unique();
            $table->string('nama_bank', 100);
            $table->string('keterangan', 255)->nullable();
            $table->tinyInteger('is_aktif')->default(1);
            $table->integer('id_status_data')->nullable();
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh')->nullable();
        });

        Schema::create('finance_mst_rekening', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedTinyInteger('id_bank');
            $table->string('no_rekening', 30)->unique();
            $table->string('nama_pemilik', 150);
            $table->string('mata_uang', 10)->default('IDR');
            $table->string('keterangan', 255)->nullable();
            $table->tinyInteger('is_aktif')->default(1);
            $table->integer('id_status_data')->nullable();
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh')->nullable();

            $table->foreign('id_bank')->references('id')->on('finance_ref_bank');
        });

        Schema::create('finance_impor_batch', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_rekening');
            $table->string('nama_file_asli', 255);
            $table->string('nama_file_simpan', 255);
            $table->unsignedInteger('ukuran_file')->default(0);
            $table->unsignedInteger('jumlah_baris_csv')->default(0);
            $table->unsignedInteger('jumlah_baris_valid')->default(0);
            $table->unsignedInteger('jumlah_duplikat')->default(0);
            $table->string('status_impor', 20)->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh')->nullable();

            $table->foreign('id_rekening')->references('id')->on('finance_mst_rekening');
        });

        Schema::create('finance_impor_log', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_batch');
            $table->unsignedInteger('baris_ke');
            $table->string('status', 20);
            $table->string('pesan', 500)->nullable();
            $table->timestamp('waktu_ubah');

            $table->foreign('id_batch')->references('id')->on('finance_impor_batch')->cascadeOnDelete();
        });

        Schema::create('finance_stg_mutasi', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('id_batch');
            $table->unsignedBigInteger('id_rekening');
            $table->date('tanggal');
            $table->text('keterangan');
            $table->string('arah', 2);
            $table->decimal('jumlah', 15, 2);
            $table->decimal('saldo', 15, 2);
            $table->string('hash_unik', 64)->unique();
            $table->unsignedInteger('baris_csv');
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh')->nullable();

            $table->foreign('id_batch')->references('id')->on('finance_impor_batch')->cascadeOnDelete();
            $table->foreign('id_rekening')->references('id')->on('finance_mst_rekening');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('finance_stg_mutasi');
        Schema::dropIfExists('finance_impor_log');
        Schema::dropIfExists('finance_impor_batch');
        Schema::dropIfExists('finance_mst_rekening');
        Schema::dropIfExists('finance_ref_bank');
    }
};
