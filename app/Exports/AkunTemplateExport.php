<?php

namespace App\Exports;

use App\Models\TipeAkun;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AkunTemplateExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        return [
            new AkunTemplateMainSheet(),
            new AkunTemplateTipeSheet(),
        ];
    }
}
