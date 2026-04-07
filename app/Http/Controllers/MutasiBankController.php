<?php

namespace App\Http\Controllers;

use App\Models\ImporBatch;
use App\Models\ImporLog;
use App\Models\Rekening;
use App\Models\StagingMutasi;
use App\Services\BcaCsvParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class MutasiBankController extends Controller
{
    /**
     * Batch list page (Blade).
     */
    public function index()
    {
        $rekenings = Rekening::where('is_aktif', 1)->get();
        return view('mutasi-bank.index', compact('rekenings'));
    }

    /**
     * Ajax: paginated batch list with filters.
     */
    public function batchList(Request $request)
    {
        $query = ImporBatch::with('rekening.bank')
            ->orderByDesc('waktu_ubah');

        if ($request->filled('id_rekening')) {
            $query->where('id_rekening', $request->id_rekening);
        }
        if ($request->filled('status')) {
            $query->where('status_impor', $request->status);
        }

        $perPage = $request->input('per_page', 15);
        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Upload form page (Blade).
     */
    public function create()
    {
        $rekenings = Rekening::where('is_aktif', 1)->with('bank')->get();
        return view('mutasi-bank.create', compact('rekenings'));
    }

    /**
     * Ajax: upload CSV, parse, return preview JSON.
     */
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120',
        ]);

        // Validate extension manually (mimes: unreliable for CSV)
        $ext = strtolower($request->file('file')->getClientOriginalExtension());
        if (!in_array($ext, ['csv', 'txt'])) {
            return response()->json([
                'success' => false,
                'message' => 'Format file tidak didukung. Hanya file .csv atau .txt yang diizinkan.',
            ], 422);
        }

        $file = $request->file('file');

        // Store temp file
        $tempName = Str::uuid() . '.csv';
        $file->storeAs('mutasi-csv', $tempName, 'local');

        $storedPath = Storage::disk('local')->path('mutasi-csv/' . $tempName);

        // Parse CSV
        $parser = new BcaCsvParser();
        $result = $parser->parse($storedPath);

        if (!empty($result->errors) && $result->dataRows === 0) {
            Storage::disk('local')->delete('mutasi-csv/' . $tempName);
            return response()->json([
                'success' => false,
                'message' => 'Gagal membaca file CSV.',
                'errors'  => $result->errors,
            ], 422);
        }

        // Check existing duplicates in DB
        $hashes = collect($result->rows)->pluck('hash_unik')->toArray();
        $existingHashes = StagingMutasi::whereIn('hash_unik', $hashes)
            ->pluck('hash_unik')
            ->toArray();
        $duplicatesCount = count($existingHashes);

        // Preview: first 50 rows
        $preview = array_slice($result->rows, 0, 50);

        return response()->json([
            'success'          => true,
            'file_temp'        => $tempName,
            'file_name'        => $file->getClientOriginalName(),
            'file_size'        => $file->getSize(),
            'csv_info'         => [
                'no_rek'    => $result->noRekDetected,
                'nama'      => $result->namaDetected,
                'mata_uang' => $result->mataUangDetected,
            ],
            'total_rows'       => $result->dataRows,
            'duplicates_count' => $duplicatesCount,
            'preview'          => $preview,
            'summary'          => [
                'saldo_awal'   => $result->saldoAwal,
                'total_kredit' => $result->totalKredit,
                'total_debet'  => $result->totalDebet,
                'saldo_akhir'  => $result->saldoAkhir,
            ],
            'errors'           => $result->errors,
        ]);
    }

    /**
     * Ajax: confirm import — write to staging.
     */
    public function store(Request $request)
    {
        $request->validate([
            'file_temp'    => 'required|string',
            'id_rekening'  => 'required|exists:finance_mst_rekening,id',
        ]);

        $tempName = $request->file_temp;
        $storedPath = Storage::disk('local')->path('mutasi-csv/' . $tempName);

        if (!file_exists($storedPath)) {
            return response()->json([
                'success' => false,
                'message' => 'File sementara tidak ditemukan. Silakan upload ulang.',
            ], 404);
        }

        // Re-parse to get fresh data
        $parser = new BcaCsvParser();
        $result = $parser->parse($storedPath);

        if ($result->dataRows === 0) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak ada data yang bisa diimpor.',
            ], 422);
        }

        $idRekening = $request->id_rekening;

        // Get original file name from upload response (stored in temp)
        $originalName = $request->input('file_name', $tempName);

        $batch = null;
        $imported = 0;
        $duplicates = 0;

        DB::transaction(function () use (
            $result, $idRekening, $tempName, $originalName, $storedPath,
            &$batch, &$imported, &$duplicates
        ) {
            $batch = ImporBatch::create([
                'id_rekening'       => $idRekening,
                'nama_file_asli'    => $originalName,
                'nama_file_simpan'  => $tempName,
                'ukuran_file'       => filesize($storedPath),
                'jumlah_baris_csv'  => $result->totalCsvRows,
                'jumlah_baris_valid' => 0,
                'jumlah_duplikat'   => 0,
                'status_impor'      => 'proses',
                'waktu_ubah'        => now(),
                'diubah_oleh'       => 1,
            ]);

            foreach ($result->rows as $row) {
                $isDuplicate = StagingMutasi::where('hash_unik', $row['hash_unik'])->exists();

                if ($isDuplicate) {
                    $duplicates++;
                    ImporLog::create([
                        'id_batch'  => $batch->id,
                        'baris_ke'  => $row['baris_csv'],
                        'status'    => 'duplikat',
                        'pesan'     => 'Transaksi duplikat terdeteksi.',
                        'waktu_ubah' => now(),
                    ]);
                    continue;
                }

                StagingMutasi::create([
                    'id_batch'    => $batch->id,
                    'id_rekening' => $idRekening,
                    'tanggal'     => $row['tanggal'],
                    'keterangan'  => $row['keterangan'],
                    'arah'        => $row['arah'],
                    'jumlah'      => $row['jumlah'],
                    'saldo'       => $row['saldo'],
                    'hash_unik'   => $row['hash_unik'],
                    'baris_csv'   => $row['baris_csv'],
                    'waktu_ubah'  => now(),
                    'diubah_oleh' => 1,
                ]);

                ImporLog::create([
                    'id_batch'  => $batch->id,
                    'baris_ke'  => $row['baris_csv'],
                    'status'    => 'ok',
                    'pesan'     => null,
                    'waktu_ubah' => now(),
                ]);

                $imported++;
            }

            $batch->update([
                'jumlah_baris_valid' => $imported,
                'jumlah_duplikat'    => $duplicates,
                'status_impor'       => 'selesai',
                'waktu_ubah'         => now(),
            ]);
        });

        return response()->json([
            'success'    => true,
            'batch_id'   => $batch->id,
            'imported'   => $imported,
            'duplicates' => $duplicates,
        ]);
    }

    /**
     * Batch detail page (Blade).
     */
    public function show($id)
    {
        $batch = ImporBatch::with('rekening.bank')->findOrFail($id);
        return view('mutasi-bank.show', compact('batch'));
    }

    /**
     * Ajax: server-side paginated staging rows with filters.
     */
    public function dataMutasi(Request $request, $id)
    {
        $query = StagingMutasi::where('id_batch', $id)
            ->orderBy('baris_csv');

        if ($request->filled('cari')) {
            $query->where('keterangan', 'like', '%' . $request->cari . '%');
        }
        if ($request->filled('tgl_dari')) {
            $query->whereDate('tanggal', '>=', $request->tgl_dari);
        }
        if ($request->filled('tgl_sampai')) {
            $query->whereDate('tanggal', '<=', $request->tgl_sampai);
        }
        if ($request->filled('arah') && in_array($request->arah, ['CR', 'DB'])) {
            $query->where('arah', $request->arah);
        }

        $perPage = $request->input('per_page', 25);
        $data = $query->paginate($perPage);

        return response()->json($data);
    }

    /**
     * Ajax: delete batch (cascade deletes staging + logs).
     */
    public function destroy($id)
    {
        $batch = ImporBatch::findOrFail($id);

        // Delete stored file
        $filePath = 'mutasi-csv/' . $batch->nama_file_simpan;
        if (Storage::disk('local')->exists($filePath)) {
            Storage::disk('local')->delete($filePath);
        }

        $batch->delete();

        return response()->json(['success' => true]);
    }

    /**
     * Download original CSV evidence file.
     */
    public function downloadFile($id)
    {
        $batch = ImporBatch::findOrFail($id);
        $filePath = Storage::disk('local')->path('mutasi-csv/' . $batch->nama_file_simpan);

        if (!file_exists($filePath)) {
            abort(404, 'File arsip tidak ditemukan.');
        }

        return response()->download($filePath, $batch->nama_file_asli);
    }
}
