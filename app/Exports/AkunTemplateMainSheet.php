<?php

namespace App\Exports;

use App\Models\TipeAkun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;

class AkunTemplateMainSheet implements FromArray, WithHeadings, WithTitle, WithStyles, WithEvents
{
    public function title(): string
    {
        return 'Template';
    }

    public function array(): array
    {
        return [
            ['', '', '', '--pilih--'],
            ['', '', '', '--pilih--'],
            ['', '', '', '--pilih--'],
            ['', '', '', '--pilih--'],
            ['', '', '', '--pilih--'],
        ];
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
                $sheet = $event->sheet->getDelegate();
                $sheet->getColumnDimension('A')->setWidth(15);
                $sheet->getColumnDimension('B')->setWidth(25);
                $sheet->getColumnDimension('C')->setWidth(15);
                $sheet->getColumnDimension('D')->setWidth(20);
                $sheet->getStyle('D2:D100')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

                $count = TipeAkun::where('is_aktif', 1)->count();
                $formula = "ref_tipe!\$A\$1:\$A\${$count}";

                $sheet = $event->sheet->getDelegate();
                for ($row = 2; $row <= 100; $row++) {
                    $validation = $sheet->getCell("D{$row}")->getDataValidation();
                    $validation->setType(DataValidation::TYPE_LIST);
                    $validation->setFormula1($formula);
                    $validation->setAllowBlank(false);
                    $validation->setShowDropDown(true);
                    $validation->setShowErrorMessage(true);
                    $validation->setErrorTitle('Tipe tidak valid');
                    $validation->setError('Pilih tipe akun dari dropdown.');
                }
            },
        ];
    }
}
