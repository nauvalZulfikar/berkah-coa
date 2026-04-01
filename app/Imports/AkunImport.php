<?php

namespace App\Imports;

use App\Models\Akun;
use App\Models\TipeAkun;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;

class AkunImport implements ToCollection, WithHeadingRow
{
    public array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $i => $row) {
            $kodeInternal = trim($row['kode'] ?? '');
            $nama         = trim($row['nama'] ?? '');
            $kodeIndukVal = trim($row['kode_induk'] ?? '');
            $tipeNama     = trim($row['tipe_akun'] ?? '');

            if (!$kodeInternal || !$nama || !$tipeNama || $tipeNama === '--pilih--') {
                continue;
            }

            // Resolve tipe_akun nama → id
            $tipe = TipeAkun::whereRaw('LOWER(tipe_akun) = ?', [strtolower($tipeNama)])->first();
            if (!$tipe) {
                $this->errors[] = "Baris ".($i+2).": tipe '$tipeNama' tidak ditemukan, dilewati.";
                continue;
            }

            // Resolve kode_induk → kode (PK)
            $kodeInduk = null;
            if ($kodeIndukVal) {
                $induk = Akun::where('kode_internal', $kodeIndukVal)->first();
                if (!$induk) {
                    $this->errors[] = "Baris ".($i+2).": induk '$kodeIndukVal' tidak ditemukan, dilewati.";
                    continue;
                }
                $kodeInduk = $induk->kode;
            }

            $level = 1;
            if ($kodeInduk) {
                $induk = Akun::find($kodeInduk);
                $level = ($induk?->level_akun ?? 0) + 1;
            }

            $existing = Akun::where('kode_internal', $kodeInternal)->first();

            if ($existing) {
                $existing->update([
                    'nama'           => $nama,
                    'kode_induk'     => $kodeInduk,
                    'id_tipe_akun'   => $tipe->id,
                    'level_akun'     => $level,
                    'waktu_ubah'     => now(),
                ]);
                $this->errors[] = "Baris ".($i+2).": '$kodeInternal' sudah ada, data diupdate.";
            } else {
                Akun::create([
                    'kode'           => (Akun::max('kode') ?? 0) + 1,
                    'kode_internal'  => $kodeInternal,
                    'nama'           => $nama,
                    'kode_induk'     => $kodeInduk,
                    'id_tipe_akun'   => $tipe->id,
                    'level_akun'     => $level,
                    'urutan'         => 0,
                    'is_aktif'       => 1,
                    'id_status_data' => 1,
                    'waktu_ubah'     => now(),
                    'diubah_oleh'    => 1,
                ]);
            }
        }
    }
}
