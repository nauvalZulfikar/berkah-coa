<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DummyDataSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('core_ref_status_data')->insert([
            ['id' => -1, 'kode_internal' => 'NON_AKTIF',   'status_data' => 'Non Aktif', 'keterangan' => 'Tidak aktif',     'waktu_ubah' => now(), 'diubah_oleh' => 1, 'is_aktif' => 1],
            ['id' =>  0, 'kode_internal' => 'DRAFT',       'status_data' => 'Draf',       'keterangan' => 'Masih rancangan', 'waktu_ubah' => now(), 'diubah_oleh' => 1, 'is_aktif' => 1],
            ['id' =>  1, 'kode_internal' => 'AKTIF',       'status_data' => 'Aktif',      'keterangan' => 'Aktif dan valid', 'waktu_ubah' => now(), 'diubah_oleh' => 1, 'is_aktif' => 1],
            ['id' =>  9, 'kode_internal' => 'HAPUS',       'status_data' => 'Hapus',      'keterangan' => 'Soft delete',     'waktu_ubah' => now(), 'diubah_oleh' => 1, 'is_aktif' => 1],
        ]);

        DB::table('gl_ref_tipe_akun')->insert([
            ['kode_internal' => 'ASET',       'tipe_akun' => 'Aset',       'keterangan' => 'Harta/aset perusahaan',            'is_aktif' => 1, 'id_status_data' => 1, 'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode_internal' => 'LIABILITAS', 'tipe_akun' => 'Liabilitas', 'keterangan' => 'Kewajiban atau utang perusahaan',   'is_aktif' => 1, 'id_status_data' => 1, 'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode_internal' => 'EKUITAS',    'tipe_akun' => 'Ekuitas',    'keterangan' => 'Modal dan saldo kepemilikan',       'is_aktif' => 1, 'id_status_data' => 1, 'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode_internal' => 'PENDAPATAN', 'tipe_akun' => 'Pendapatan', 'keterangan' => 'Pendapatan usaha maupun non-usaha', 'is_aktif' => 1, 'id_status_data' => 1, 'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode_internal' => 'BEBAN',      'tipe_akun' => 'Beban',      'keterangan' => 'Biaya atau beban operasional',      'is_aktif' => 1, 'id_status_data' => 1, 'waktu_ubah' => now(), 'diubah_oleh' => 1],
        ]);

        DB::table('gl_mst_akun')->insert([
            ['kode' => 1000, 'kode_internal' => 'ASET',             'nama' => 'Aset',                  'kode_induk' => null, 'id_tipe_akun' => 1, 'level_akun' => 1, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1100, 'kode_internal' => 'ASET_LANCAR',      'nama' => 'Aset Lancar',           'kode_induk' => 1000, 'id_tipe_akun' => 1, 'level_akun' => 2, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1110, 'kode_internal' => 'KAS_BANK',         'nama' => 'Kas dan Bank',          'kode_induk' => 1100, 'id_tipe_akun' => 1, 'level_akun' => 3, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1111, 'kode_internal' => 'KAS_UTAMA',        'nama' => 'Kas Utama',             'kode_induk' => 1110, 'id_tipe_akun' => 1, 'level_akun' => 4, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1112, 'kode_internal' => 'BANK_BCA',         'nama' => 'Bank BCA',              'kode_induk' => 1110, 'id_tipe_akun' => 1, 'level_akun' => 4, 'urutan' => 2, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1120, 'kode_internal' => 'PIUTANG_USAHA',    'nama' => 'Piutang Usaha',         'kode_induk' => 1100, 'id_tipe_akun' => 1, 'level_akun' => 3, 'urutan' => 2, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1130, 'kode_internal' => 'PERSEDIAAN',       'nama' => 'Persediaan Barang',     'kode_induk' => 1100, 'id_tipe_akun' => 1, 'level_akun' => 3, 'urutan' => 3, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1200, 'kode_internal' => 'ASET_TETAP',       'nama' => 'Aset Tetap',            'kode_induk' => 1000, 'id_tipe_akun' => 1, 'level_akun' => 2, 'urutan' => 2, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 1210, 'kode_internal' => 'KENDARAAN',        'nama' => 'Kendaraan',             'kode_induk' => 1200, 'id_tipe_akun' => 1, 'level_akun' => 3, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 2000, 'kode_internal' => 'LIABILITAS',       'nama' => 'Liabilitas',            'kode_induk' => null, 'id_tipe_akun' => 2, 'level_akun' => 1, 'urutan' => 2, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 2100, 'kode_internal' => 'HUTANG_USAHA',     'nama' => 'Hutang Usaha',          'kode_induk' => 2000, 'id_tipe_akun' => 2, 'level_akun' => 2, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 2200, 'kode_internal' => 'HUTANG_PAJAK',     'nama' => 'Hutang Pajak',          'kode_induk' => 2000, 'id_tipe_akun' => 2, 'level_akun' => 2, 'urutan' => 2, 'is_aktif' => 0, 'id_status_data' => -1, 'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 3000, 'kode_internal' => 'EKUITAS',          'nama' => 'Ekuitas',               'kode_induk' => null, 'id_tipe_akun' => 3, 'level_akun' => 1, 'urutan' => 3, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 3100, 'kode_internal' => 'MODAL',            'nama' => 'Modal Pemilik',         'kode_induk' => 3000, 'id_tipe_akun' => 3, 'level_akun' => 2, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 4000, 'kode_internal' => 'PENDAPATAN',       'nama' => 'Pendapatan',            'kode_induk' => null, 'id_tipe_akun' => 4, 'level_akun' => 1, 'urutan' => 4, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 4100, 'kode_internal' => 'PEND_PENJUALAN',   'nama' => 'Pendapatan Penjualan',  'kode_induk' => 4000, 'id_tipe_akun' => 4, 'level_akun' => 2, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 5000, 'kode_internal' => 'BEBAN',            'nama' => 'Beban',                 'kode_induk' => null, 'id_tipe_akun' => 5, 'level_akun' => 1, 'urutan' => 5, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 5100, 'kode_internal' => 'HPP',              'nama' => 'Harga Pokok Penjualan', 'kode_induk' => 5000, 'id_tipe_akun' => 5, 'level_akun' => 2, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 5200, 'kode_internal' => 'BEBAN_OPERASIONAL', 'nama' => 'Beban Operasional',    'kode_induk' => 5000, 'id_tipe_akun' => 5, 'level_akun' => 2, 'urutan' => 2, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
            ['kode' => 5210, 'kode_internal' => 'BEBAN_GAJI',       'nama' => 'Beban Gaji',            'kode_induk' => 5200, 'id_tipe_akun' => 5, 'level_akun' => 3, 'urutan' => 1, 'is_aktif' => 1, 'id_status_data' => 1,  'waktu_ubah' => now(), 'diubah_oleh' => 1],
        ]);
    }
}
