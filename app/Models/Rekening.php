<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rekening extends Model
{
    protected $table = 'finance_mst_rekening';
    public $timestamps = false;

    protected $fillable = [
        'id_bank', 'no_rekening', 'nama_pemilik', 'mata_uang',
        'keterangan', 'is_aktif', 'id_status_data', 'waktu_ubah', 'diubah_oleh',
    ];

    protected $casts = [
        'is_aktif'   => 'integer',
        'waktu_ubah' => 'datetime',
    ];

    public function bank()
    {
        return $this->belongsTo(Bank::class, 'id_bank', 'id');
    }

    public function imporBatch()
    {
        return $this->hasMany(ImporBatch::class, 'id_rekening', 'id');
    }
}
