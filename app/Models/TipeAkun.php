<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipeAkun extends Model
{
    protected $table = 'gl_ref_tipe_akun';
    public $timestamps = false;

    protected $fillable = ['kode_internal', 'tipe_akun', 'keterangan', 'is_aktif', 'id_status_data'];
}
