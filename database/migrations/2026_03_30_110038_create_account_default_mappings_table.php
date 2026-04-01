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
        Schema::create('account_default_mappings', function (Blueprint $table) {
            $table->id();
            $table->string('nama_modul', 100);
            $table->string('nama_proses', 150);
            $table->unsignedBigInteger('akun_id')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();

            $table->foreign('akun_id')->references('id')->on('chart_of_accounts')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('account_default_mappings');
    }
};
