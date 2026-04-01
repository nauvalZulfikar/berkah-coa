<?php

namespace App\Exports;

use App\Models\TipeAkun;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class AkunTemplateTipeSheet implements FromArray, WithTitle, WithEvents
{
    public function title(): string
    {
        return 'ref_tipe';
    }

    public function array(): array
    {
        return TipeAkun::where('is_aktif', 1)
            ->pluck('tipe_akun')
            ->map(fn($t) => [$t])
            ->toArray();
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                // Hide this sheet
                $event->sheet->getDelegate()->getParent()->getSheetByName('ref_tipe')
                    ->setSheetState(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::SHEETSTATE_HIDDEN);
            },
        ];
    }
}
