<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('kode_akun', 20)->unique();
            $table->string('nama_akun', 150);
            $table->unsignedBigInteger('akun_induk_id')->nullable();
            $table->enum('tipe_akun', ['aset', 'liabilitas', 'ekuitas', 'pendapatan', 'beban']);
            $table->enum('klasifikasi_laporan', ['neraca', 'laba_rugi', 'arus_kas', 'lainnya']);
            $table->boolean('status_aktif')->default(true);
            $table->string('mata_uang', 10)->nullable();
            $table->string('cabang', 100)->nullable();
            $table->string('proyek', 100)->nullable();
            $table->boolean('is_control_account')->default(false);
            $table->boolean('is_akun_pajak')->default(false);
            $table->boolean('is_akun_persediaan')->default(false);
            $table->boolean('is_akun_hpp')->default(false);
            $table->boolean('is_akun_kas_bank')->default(false);
            $table->text('keterangan')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('akun_induk_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chart_of_accounts');
    }
};
