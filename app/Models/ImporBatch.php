<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImporBatch extends Model
{
    protected $table = 'finance_impor_batch';
    public $timestamps = false;

    protected $fillable = [
        'id_rekening', 'nama_file_asli', 'nama_file_simpan',
        'ukuran_file', 'jumlah_baris_csv', 'jumlah_baris_valid',
        'jumlah_duplikat', 'status_impor', 'catatan',
        'waktu_ubah', 'diubah_oleh',
    ];

    protected $casts = [
        'waktu_ubah' => 'datetime',
    ];

    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'id_rekening', 'id');
    }

    public function logs()
    {
        return $this->hasMany(ImporLog::class, 'id_batch', 'id');
    }

    public function mutasi()
    {
        return $this->hasMany(StagingMutasi::class, 'id_batch', 'id');
    }
}
