<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImporLog extends Model
{
    protected $table = 'finance_impor_log';
    public $timestamps = false;

    protected $fillable = [
        'id_batch', 'baris_ke', 'status', 'pesan', 'waktu_ubah',
    ];

    protected $casts = [
        'waktu_ubah' => 'datetime',
    ];

    public function batch()
    {
        return $this->belongsTo(ImporBatch::class, 'id_batch', 'id');
    }
}
