<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Akun extends Model
{
    protected $table = 'gl_mst_akun';
    protected $primaryKey = 'kode';
    public $incrementing = false;
    protected $keyType = 'int';
    public $timestamps = false;

    protected $fillable = [
        'kode', 'kode_internal', 'nama', 'kode_induk', 'id_tipe_akun',
        'level_akun', 'urutan', 'keterangan', 'is_aktif', 'id_status_data',
        'waktu_ubah', 'diubah_oleh',
    ];

    protected $casts = [
        'is_aktif' => 'boolean',
        'waktu_ubah' => 'datetime',
    ];

    public function tipeAkun()
    {
        return $this->belongsTo(TipeAkun::class, 'id_tipe_akun', 'id');
    }

    public function induk()
    {
        return $this->belongsTo(Akun::class, 'kode_induk', 'kode');
    }

    public function anak()
    {
        return $this->hasMany(Akun::class, 'kode_induk', 'kode');
    }

    public function statusData()
    {
        return $this->belongsTo(StatusData::class, 'id_status_data', 'id');
    }
}
