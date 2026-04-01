@extends('layouts.app')

@section('content')
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0"><i class="bi bi-plus-circle me-2"></i>TC-01 — Buat Akun Baru</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('akun.store') }}">
                    @csrf
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Kode Internal <span class="text-danger">*</span></label>
                            <input type="text" name="kode_internal"
                                class="form-control @error('kode_internal') is-invalid @enderror"
                                value="{{ old('kode_internal') }}"
                                placeholder="Mis. KAS_UTAMA" required maxlength="30"
                                style="text-transform:uppercase">
                            @error('kode_internal')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text">Huruf kapital, tanpa spasi (gunakan _)</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Nama Akun <span class="text-danger">*</span></label>
                            <input type="text" name="nama"
                                class="form-control @error('nama') is-invalid @enderror"
                                value="{{ old('nama') }}"
                                placeholder="Mis. Kas Utama" required maxlength="150">
                            @error('nama')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Tipe Akun <span class="text-danger">*</span></label>
                            <select name="id_tipe_akun"
                                class="form-select @error('id_tipe_akun') is-invalid @enderror" required>
                                <option value="">-- Pilih Tipe --</option>
                                @foreach($tipeAkuns as $t)
                                    <option value="{{ $t->id }}" {{ old('id_tipe_akun') == $t->id ? 'selected' : '' }}>
                                        {{ $t->tipe_akun }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_tipe_akun')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Akun Induk</label>
                            <select name="kode_induk"
                                class="form-select @error('kode_induk') is-invalid @enderror">
                                <option value="">-- Tidak Ada (akun root) --</option>
                                @foreach($akunIndukList as $a)
                                    <option value="{{ $a->kode }}" {{ old('kode_induk') == $a->kode ? 'selected' : '' }}>
                                        {{ $a->kode_internal }} — {{ $a->nama }}
                                    </option>
                                @endforeach
                            </select>
                            @error('kode_induk')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    <hr>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i>Simpan Akun
                        </button>
                        <a href="{{ route('akun.index') }}" class="btn btn-outline-secondary">Batal</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
