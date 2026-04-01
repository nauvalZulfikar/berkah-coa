<?php

namespace App\Exports;

use App\Models\TipeAkun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AkunTemplateExport implements FromArray, WithHeadings, WithStyles, WithEvents
{
    public function array(): array
    {
        return [];
    }

    public function headings(): array
    {
        return ['kode', 'nama', 'kode_induk', 'tipe_akun'];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $tipeList = TipeAkun::where('is_aktif', 1)->pluck('tipe_akun')->implode(',');

                $sheet = $event->sheet->getDelegate();
                for ($row = 2; $row <= 100; $row++) {
                    $validation = $sheet->getCell("D{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1('"'.$tipeList.'"');
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Tipe tidak valid');
                    $validation->setError('Pilih tipe akun dari dropdown.');
                }
            },
        ];
    }
}
