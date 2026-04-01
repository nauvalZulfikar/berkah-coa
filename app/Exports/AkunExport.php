<?php

namespace App\Exports;

use App\Models\Akun;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AkunExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Akun::with(['tipeAkun', 'induk', 'statusData'])
            ->orderBy('kode')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Kode', 'Kode Internal', 'Nama Akun', 'Kode Induk', 'Nama Induk',
            'Tipe Akun', 'Level', 'Urutan', 'Keterangan', 'Status Aktif', 'Status Data',
        ];
    }

    public function map($akun): array
    {
        return [
            $akun->kode,
            $akun->kode_internal,
            $akun->nama,
            $akun->kode_induk,
            $akun->induk?->nama,
            $akun->tipeAkun?->tipe_akun,
            $akun->level_akun,
            $akun->urutan,
            $akun->keterangan,
            $akun->is_aktif ? 'Aktif' : 'Non Aktif',
            $akun->statusData?->status_data,
        ];
    }
}
