<?php

namespace App\Services;

use Carbon\Carbon;

class BcaCsvParser
{
    /**
     * Parse a BCA KlikBCA Bisnis CSV file.
     *
     * Handles two known formats:
     * - Format 1: Nominal quoted with commas, e.g. " 20,000 "
     * - Format 2: Nominal clean decimal, e.g. 20000.00
     *
     * Both formats share the same header metadata structure (rows 1-3)
     * and summary footer (Saldo Awal, Kredit, Debet, Saldo Akhir).
     */
    public function parse(string $filePath): BcaCsvParseResult
    {
        $result = new BcaCsvParseResult();

        $content = file_get_contents($filePath);
        if ($content === false) {
            $result->errors[] = 'Tidak dapat membaca file.';
            return $result;
        }

        // Detect and convert encoding if needed
        $encoding = mb_detect_encoding($content, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
        if ($encoding && $encoding !== 'UTF-8') {
            $content = mb_convert_encoding($content, 'UTF-8', $encoding);
        }

        // Normalize line endings
        $content = str_replace("\r\n", "\n", $content);
        $content = str_replace("\r", "\n", $content);
        $lines = explode("\n", $content);

        $result->totalCsvRows = count($lines);

        $state = 'header'; // header -> columns -> data -> footer
        $headerRowIndex = -1;
        $isFormat1 = false; // Format 1 = quoted commas, Format 2 = clean decimal
        $rowNumber = 0;

        foreach ($lines as $lineIndex => $rawLine) {
            $trimmed = trim($rawLine);
            if ($trimmed === '') {
                $result->skippedRows++;
                continue;
            }

            // --- HEADER METADATA (rows 1-3) ---
            if ($state === 'header') {
                if ($this->startsWithIgnoreCase($trimmed, 'No. Rekening')) {
                    $result->noRekDetected = $this->extractMetaValue($trimmed);
                    $result->skippedRows++;
                    continue;
                }
                if ($this->startsWithIgnoreCase($trimmed, 'Nama')) {
                    $result->namaDetected = $this->extractMetaValue($trimmed);
                    $result->skippedRows++;
                    continue;
                }
                if ($this->startsWithIgnoreCase($trimmed, 'Mata Uang')) {
                    $result->mataUangDetected = $this->extractMetaValue($trimmed);
                    $result->skippedRows++;
                    continue;
                }
                // Check if this is the column header row
                if ($this->startsWithIgnoreCase($trimmed, 'Tanggal')) {
                    $state = 'data';
                    $headerRowIndex = $lineIndex;
                    // Detect format by checking if "Jumlah" column has a leading space
                    $isFormat1 = str_contains($rawLine, '" Jumlah "') || str_contains($rawLine, '," Jumlah "');
                    if (!$isFormat1) {
                        $isFormat1 = str_contains($rawLine, ', Jumlah ');
                    }
                    $result->skippedRows++;
                    continue;
                }
                $result->skippedRows++;
                continue;
            }

            // --- FOOTER SUMMARY ---
            if ($this->startsWithIgnoreCase($trimmed, 'Saldo Awal')) {
                $state = 'footer';
                $result->saldoAwal = $this->extractSummaryValue($trimmed);
                $result->skippedRows++;
                continue;
            }
            if ($state === 'footer') {
                if ($this->startsWithIgnoreCase($trimmed, 'Kredit')) {
                    $result->totalKredit = $this->extractSummaryValue($trimmed);
                } elseif ($this->startsWithIgnoreCase($trimmed, 'Debet')) {
                    $result->totalDebet = $this->extractSummaryValue($trimmed);
                } elseif ($this->startsWithIgnoreCase($trimmed, 'Saldo Akhir')) {
                    $result->saldoAkhir = $this->extractSummaryValue($trimmed);
                }
                $result->skippedRows++;
                continue;
            }

            // --- DATA ROWS ---
            if ($state === 'data') {
                $rowNumber++;
                $parsed = $this->parseDataRow($rawLine, $isFormat1, $result->noRekDetected, $rowNumber);

                if ($parsed === null) {
                    $result->errors[] = "Baris {$rowNumber}: Gagal parse data.";
                    $result->skippedRows++;
                    continue;
                }

                $result->rows[] = $parsed;
            }
        }

        $result->dataRows = count($result->rows);
        return $result;
    }

    /**
     * Parse a single data row from BCA CSV.
     *
     * Format 1 columns: Tanggal, Keterangan, Cabang, " Jumlah ", CR/DB, Saldo
     *   - Jumlah is quoted with commas: " 20,000 "
     *
     * Format 2 columns: Tanggal, Keterangan, Cabang, Jumlah, (empty), CR/DB, Saldo
     *   - Jumlah is clean decimal: 20000.00
     */
    private function parseDataRow(string $rawLine, bool $isFormat1, ?string $noRek, int $rowNumber): ?array
    {
        // Use str_getcsv to handle quoted fields properly
        $cols = str_getcsv($rawLine, ',');

        if (count($cols) < 5) {
            return null;
        }

        // --- Tanggal ---
        $tanggalRaw = trim($cols[0] ?? '', " '\t");
        $tanggal = $this->parseDate($tanggalRaw);
        if (!$tanggal) {
            return null;
        }

        // --- Keterangan ---
        $keterangan = $this->cleanKeterangan($cols[1] ?? '');
        if (empty($keterangan)) {
            return null;
        }

        // --- Cabang ---
        $cabang = trim($cols[2] ?? '', " '\t");

        // --- Jumlah + Arah ---
        $jumlah = 0.0;
        $arah = '';
        $saldo = 0.0;

        if ($isFormat1) {
            // Format 1: col[3] = " 20,000 " (quoted with commas), col[4] = CR/DB, col[5] = saldo
            $jumlah = $this->parseNominal($cols[3] ?? '');
            $arah = strtoupper(trim($cols[4] ?? ''));
            $saldo = $this->parseNominal($cols[5] ?? '');
        } else {
            // Format 2: col[3] = 20000.00, col[4] = CR/DB, col[5] = saldo
            $jumlah = $this->parseNominal($cols[3] ?? '');
            $arah = strtoupper(trim($cols[4] ?? ''));
            $saldo = $this->parseNominal($cols[5] ?? '');
        }

        // Validate arah
        if (!in_array($arah, ['CR', 'DB'])) {
            // Some CR rows in BCA CSV don't have explicit "CR" - they just show amount without DB
            // If arah is empty and jumlah > 0, treat as CR
            if ($arah === '' && $jumlah > 0) {
                $arah = 'CR';
            } else {
                return null;
            }
        }

        // --- Hash for duplicate detection ---
        $hashSource = implode('|', [
            $noRek ?? '',
            $tanggal,
            $keterangan,
            number_format($jumlah, 2, '.', ''),
            $arah,
            number_format($saldo, 2, '.', ''),
        ]);
        $hashUnik = hash('sha256', $hashSource);

        return [
            'tanggal'    => $tanggal,
            'keterangan' => $keterangan,
            'arah'       => $arah,
            'jumlah'     => round($jumlah, 2),
            'saldo'      => round($saldo, 2),
            'hash_unik'  => $hashUnik,
            'baris_csv'  => $rowNumber,
        ];
    }

    /**
     * Parse date from BCA format: DD/MM/YYYY (with optional apostrophe prefix).
     */
    private function parseDate(string $raw): ?string
    {
        $raw = ltrim($raw, "'");
        $raw = trim($raw);

        if (empty($raw)) {
            return null;
        }

        try {
            return Carbon::createFromFormat('d/m/Y', $raw)->format('Y-m-d');
        } catch (\Exception $e) {
            // Try alternative formats
            try {
                return Carbon::createFromFormat('d/m/y', $raw)->format('Y-m-d');
            } catch (\Exception $e) {
                return null;
            }
        }
    }

    /**
     * Parse nominal value from BCA CSV.
     * Handles: " 20,000 " (Format 1) and 20000.00 (Format 2).
     */
    private function parseNominal(string $raw): float
    {
        $raw = trim($raw, " \t\"'");

        if ($raw === '' || $raw === '-') {
            return 0.0;
        }

        // If contains comma but no dot, it's Format 1 (thousand separator comma)
        if (str_contains($raw, ',') && !str_contains($raw, '.')) {
            $raw = str_replace(',', '', $raw);
        }
        // If contains both comma and dot, determine which is decimal
        elseif (str_contains($raw, ',') && str_contains($raw, '.')) {
            // BCA uses comma as thousand separator, dot as decimal
            $raw = str_replace(',', '', $raw);
        }

        $raw = str_replace(' ', '', $raw);
        return (float) $raw;
    }

    /**
     * Clean keterangan text: collapse whitespace, trim.
     */
    private function cleanKeterangan(string $raw): string
    {
        $raw = trim($raw);
        $raw = preg_replace('/\s+/', ' ', $raw);
        return $raw;
    }

    /**
     * Extract metadata value from header rows like "No. Rekening,=,'8382556789"
     */
    private function extractMetaValue(string $line): string
    {
        $parts = str_getcsv($line, ',');
        // Value is typically the 3rd element (index 2)
        $value = trim($parts[2] ?? '', " '\t\"");
        return $value;
    }

    /**
     * Extract summary value from footer rows like "Saldo Awal,=,18807760.76"
     */
    private function extractSummaryValue(string $line): float
    {
        $parts = str_getcsv($line, ',');
        $value = trim($parts[2] ?? '', " '\t\"");
        return $this->parseNominal($value);
    }

    /**
     * Case-insensitive starts-with check.
     */
    private function startsWithIgnoreCase(string $haystack, string $needle): bool
    {
        return stripos($haystack, $needle) === 0;
    }
}
