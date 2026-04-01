<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('core_ref_status_data', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->string('kode_internal', 30);
            $table->string('status_data', 30);
            $table->string('keterangan', 255)->nullable();
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh');
            $table->tinyInteger('is_aktif')->default(1);
        });

        Schema::create('gl_ref_tipe_akun', function (Blueprint $table) {
            $table->tinyIncrements('id');
            $table->string('kode_internal', 20)->unique();
            $table->string('tipe_akun', 50);
            $table->string('keterangan', 255)->nullable();
            $table->tinyInteger('is_aktif')->default(1);
            $table->integer('id_status_data')->nullable();
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh')->nullable();
        });

        Schema::create('gl_mst_akun', function (Blueprint $table) {
            $table->unsignedBigInteger('kode')->primary();
            $table->string('kode_internal', 30)->unique();
            $table->string('nama', 150);
            $table->unsignedBigInteger('kode_induk')->nullable();
            $table->unsignedTinyInteger('id_tipe_akun')->nullable();
            $table->unsignedTinyInteger('level_akun')->nullable();
            $table->unsignedSmallInteger('urutan')->nullable();
            $table->string('keterangan', 255)->nullable();
            $table->tinyInteger('is_aktif')->default(1);
            $table->integer('id_status_data')->nullable();
            $table->timestamp('waktu_ubah');
            $table->smallInteger('diubah_oleh')->nullable();

            $table->foreign('kode_induk')->references('kode')->on('gl_mst_akun')->nullOnDelete();
            $table->foreign('id_tipe_akun')->references('id')->on('gl_ref_tipe_akun')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('gl_mst_akun');
        Schema::dropIfExists('gl_ref_tipe_akun');
        Schema::dropIfExists('core_ref_status_data');
    }
};
