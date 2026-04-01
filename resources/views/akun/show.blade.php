@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-9">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0"><i class="bi bi-info-circle me-2"></i>Detail Akun</h5>
            <a href="{{ route('akun.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Kembali
            </a>
        </div>

        <div class="card shadow-sm mb-3">
            <div class="card-header">
                <strong>{{ $akun->kode_internal }}</strong> — {{ $akun->nama }}
            </div>
            <div class="card-body">
                <div class="row g-2">
                    <div class="col-md-6">
                        <small class="text-muted">Kode</small>
                        <div class="fw-bold">{{ $akun->kode }}</div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Kode Internal</small>
                        <div><code>{{ $akun->kode_internal }}</code></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Tipe Akun</small>
                        <div><span class="badge bg-secondary">{{ $akun->tipeAkun?->tipe_akun ?? '—' }}</span></div>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted">Akun Induk</small>
                        <div>
                            @if($akun->induk)
                                <a href="{{ route('akun.show', $akun->induk->kode) }}">
                                    {{ $akun->induk->kode_internal }} — {{ $akun->induk->nama }}
                                </a>
                            @else
                                <span class="text-muted">— (root)</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Level</small>
                        <div>{{ $akun->level_akun }}</div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Urutan</small>
                        <div>{{ $akun->urutan }}</div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Status Aktif</small>
                        <div>
                            @if($akun->is_aktif)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Non Aktif</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3">
                        <small class="text-muted">Status Data</small>
                        <div>{{ $akun->statusData?->status_data ?? '—' }}</div>
                    </div>
                    @if($akun->keterangan)
                    <div class="col-12">
                        <small class="text-muted">Keterangan</small>
                        <div>{{ $akun->keterangan }}</div>
                    </div>
                    @endif
                    <div class="col-md-6">
                        <small class="text-muted">Terakhir Diubah</small>
                        <div>{{ $akun->waktu_ubah?->format('d M Y H:i') ?? '—' }}</div>
                    </div>
                </div>
            </div>
        </div>

        @if($akun->anak->count() > 0)
        <div class="card shadow-sm">
            <div class="card-header">
                <i class="bi bi-diagram-3 me-1"></i>Akun Turunan ({{ $akun->anak->count() }})
            </div>
            <div class="card-body p-0">
                <table class="table table-sm mb-0">
                    <thead class="table-light">
                        <tr><th>Kode</th><th>Kode Internal</th><th>Nama</th><th>Tipe</th><th>Status</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach($akun->anak as $anak)
                        <tr>
                            <td>{{ $anak->kode }}</td>
                            <td><code>{{ $anak->kode_internal }}</code></td>
                            <td>{{ $anak->nama }}</td>
                            <td><span class="badge bg-secondary badge-tipe">{{ $anak->tipeAkun?->tipe_akun }}</span></td>
                            <td>
                                @if($anak->is_aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-danger">Non Aktif</span>
                                @endif
                            </td>
                            <td><a href="{{ route('akun.show', $anak->kode) }}" class="btn btn-outline-primary btn-sm py-0"><i class="bi bi-eye"></i></a></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>
@endsection
