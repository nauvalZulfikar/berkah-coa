<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bank extends Model
{
    protected $table = 'finance_ref_bank';
    public $timestamps = false;

    protected $fillable = [
        'kode_internal', 'nama_bank', 'keterangan',
        'is_aktif', 'id_status_data', 'waktu_ubah', 'diubah_oleh',
    ];

    protected $casts = [
        'is_aktif'   => 'integer',
        'waktu_ubah' => 'datetime',
    ];

    public function rekening()
    {
        return $this->hasMany(Rekening::class, 'id_bank', 'id');
    }
}
