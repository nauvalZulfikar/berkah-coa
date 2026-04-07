<?php

namespace App\Services;

class BcaCsvParseResult
{
    public array $rows = [];
    public int $totalCsvRows = 0;
    public int $dataRows = 0;
    public int $skippedRows = 0;

    public ?string $noRekDetected = null;
    public ?string $namaDetected = null;
    public ?string $mataUangDetected = null;

    public ?float $saldoAwal = null;
    public ?float $totalKredit = null;
    public ?float $totalDebet = null;
    public ?float $saldoAkhir = null;

    public array $errors = [];
}
