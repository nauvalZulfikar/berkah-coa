<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StagingMutasi extends Model
{
    protected $table = 'finance_stg_mutasi';
    public $timestamps = false;

    protected $fillable = [
        'id_batch', 'id_rekening', 'tanggal', 'keterangan',
        'arah', 'jumlah', 'saldo', 'hash_unik', 'baris_csv',
        'waktu_ubah', 'diubah_oleh',
    ];

    protected $casts = [
        'tanggal'    => 'date',
        'jumlah'     => 'decimal:2',
        'saldo'      => 'decimal:2',
        'waktu_ubah' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(ImporBatch::class, 'id_batch', 'id');
    }

    public function rekening()
    {
        return $this->belongsTo(Rekening::class, 'id_rekening', 'id');
    }
}
