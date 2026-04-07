<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BankRekeningSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('finance_ref_bank')->insert([
            [
                'kode_internal' => 'BCA',
                'nama_bank'     => 'Bank Central Asia',
                'keterangan'    => 'BCA KlikBCA Bisnis CSV format',
                'is_aktif'      => 1,
                'id_status_data' => 1,
                'waktu_ubah'    => now(),
                'diubah_oleh'   => 1,
            ],
        ]);

        DB::table('finance_mst_rekening')->insert([
            [
                'id_bank'        => 1,
                'no_rekening'    => '8382556789',
                'nama_pemilik'   => 'OLIS SUSANTI',
                'mata_uang'      => 'IDR',
                'keterangan'     => 'Rekening Tahapan BCA KCP Jamika',
                'is_aktif'       => 1,
                'id_status_data' => 1,
                'waktu_ubah'     => now(),
                'diubah_oleh'    => 1,
            ],
        ]);
    }
}
