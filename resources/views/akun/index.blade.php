@extends('layouts.app')

@push('styles')
<style>
.chevron-icon {
    display: inline-block;
    transition: transform .2s ease;
    transform: rotate(90deg);
}
.chevron-icon.collapsed {
    transform: rotate(0deg);
}
.toggle-switch {
    appearance: none;
    width: 2.25rem;
    height: 1.25rem;
    background: #d1d5db;
    border-radius: 9999px;
    position: relative;
    cursor: pointer;
    transition: background .2s;
}
.toggle-switch:checked {
    background: #2563eb;
}
.toggle-switch::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 2px;
    width: 1rem;
    height: 1rem;
    background: white;
    border-radius: 9999px;
    transition: transform .2s;
}
.toggle-switch:checked::after {
    transform: translateX(1rem);
}
</style>
@endpush

@section('content')
<div x-data="coaApp()" x-init="init()">

{{-- Header --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3">
    <h4 class="text-xl font-bold flex items-center gap-2"><i class="bi bi-list-ul"></i>Chart of Accounts</h4>
    <div class="flex flex-wrap gap-2">
        <button @click="modalCreate = true" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-1">
            <i class="bi bi-plus-circle"></i>Buat Akun Baru
        </button>
        <a href="{{ route('akun.template') }}" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 flex items-center gap-1 text-gray-700 no-underline">
            <i class="bi bi-file-earmark-arrow-down"></i>Download Template
        </a>
        <button @click="modalImport = true" class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center gap-1">
            <i class="bi bi-file-earmark-arrow-up"></i>Import Excel
        </button>
        <a href="{{ route('akun.export') }}" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700 flex items-center gap-1 no-underline">
            <i class="bi bi-file-earmark-excel"></i>Export Excel
        </a>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg shadow-sm mb-3">
    <div class="p-3">
        <form method="GET" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-2">
            <input type="text" name="cari" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none"
                placeholder="Cari kode / nama akun..." value="{{ request('cari') }}">
            <select name="tipe" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Semua Tipe --</option>
                @foreach($tipeAkuns as $t)
                    <option value="{{ $t->id }}" {{ request('tipe') == $t->id ? 'selected' : '' }}>{{ $t->tipe_akun }}</option>
                @endforeach
            </select>
            <select name="status" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Semua Status --</option>
                @foreach($statusList as $s)
                    <option value="{{ $s->id }}" {{ request('status') == $s->id ? 'selected' : '' }}>{{ $s->status_data }}</option>
                @endforeach
            </select>
            <div class="flex gap-1">
                <button type="submit" class="flex-1 px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700">
                    <i class="bi bi-search"></i> Filter
                </button>
                <a href="{{ route('akun.index') }}" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 text-gray-700 no-underline">Reset</a>
            </div>
        </form>
    </div>
</div>

{{-- Table --}}
<div class="bg-white rounded-lg shadow-sm">
    <div class="overflow-auto" style="max-height:65vh;">
        <table class="w-full text-sm" style="min-width:700px;">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600" style="width:100px">Kode</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Nama Akun</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 ">Induk</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600 " style="width:120px">Tipe</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600" style="width:140px">Action</th>
                </tr>
            </thead>
            <tbody>
                @if($roots->count())
                    <x-akun-rows :nodes="$roots" :allAkuns="$allAkuns" :depth="0" />
                @else
                    <tr><td colspan="5" class="text-center text-gray-400 py-8">Belum ada data akun.</td></tr>
                @endif
            </tbody>
        </table>
    </div>
    <div class="px-3 py-2 text-gray-500 text-xs">Total: {{ $allAkuns->count() }} akun</div>
</div>

{{-- Modal: Buat Akun Baru --}}
<div x-show="modalCreate" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center bg-black/50" style="display:none" @click.self="modalCreate = false">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
        <div class="bg-blue-600 text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
            <h6 class="font-semibold flex items-center gap-2 text-sm"><i class="bi bi-plus-circle"></i>Buat Akun Baru</h6>
            <button @click="modalCreate = false" class="text-white hover:text-gray-200">&times;</button>
        </div>
        <form method="POST" action="{{ route('akun.store') }}" @submit="$event.target.querySelector('[type=submit]').disabled=true; $event.target.querySelector('[type=submit]').innerHTML='<span class=\'inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-1\'></span>Menyimpan...'">
            @csrf
            <div class="p-4 space-y-3">
                <div>
                    <label class="block text-sm font-semibold mb-1">Kode Internal <span class="text-red-500">*</span></label>
                    <input type="text" name="kode_internal"
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none @error('kode_internal') border-red-500 @enderror"
                        value="{{ old('kode_internal') }}"
                        placeholder="Mis. 1000, 1100" required maxlength="30">
                    @error('kode_internal')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Akun <span class="text-red-500">*</span></label>
                    <input type="text" name="nama"
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none @error('nama') border-red-500 @enderror"
                        value="{{ old('nama') }}"
                        placeholder="Mis. Kas Utama" required maxlength="150">
                    @error('nama')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tipe Akun <span class="text-red-500">*</span></label>
                    <select name="id_tipe_akun"
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none @error('id_tipe_akun') border-red-500 @enderror" required>
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($tipeAkuns as $t)
                            <option value="{{ $t->id }}" {{ old('id_tipe_akun') == $t->id ? 'selected' : '' }}>{{ $t->tipe_akun }}</option>
                        @endforeach
                    </select>
                    @error('id_tipe_akun')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Akun Induk</label>
                    <select name="kode_induk"
                        class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none @error('kode_induk') border-red-500 @enderror">
                        <option value="">-- Tidak Ada (akun root) --</option>
                        @foreach($allAkuns as $a)
                            <option value="{{ $a->kode }}" {{ old('kode_induk') == $a->kode ? 'selected' : '' }}>{{ $a->kode_internal }} — {{ $a->nama }}</option>
                        @endforeach
                    </select>
                    @error('kode_induk')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
            <div class="px-4 py-3 border-t flex justify-end gap-2">
                <button type="button" @click="modalCreate = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center gap-1">
                    <i class="bi bi-save"></i>Simpan Akun
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Import Excel --}}
<div x-show="modalImport" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center bg-black/50" style="display:none" @click.self="modalImport = false">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
        <div class="bg-yellow-500 text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
            <h6 class="font-semibold flex items-center gap-2 text-sm"><i class="bi bi-file-earmark-arrow-up"></i>Import Excel</h6>
            <button @click="modalImport = false" class="text-white hover:text-gray-200">&times;</button>
        </div>
        <form method="POST" action="{{ route('akun.import') }}" enctype="multipart/form-data" @submit="$event.target.querySelector('[type=submit]').disabled=true; $event.target.querySelector('[type=submit]').innerHTML='<span class=\'inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin mr-1\'></span>Importing...'">
            @csrf
            <div class="p-4">
                <p class="text-sm text-gray-500 mb-2">Upload file <strong>.xlsx</strong> dengan kolom sesuai template: <code class="text-pink-600">kode, nama, kode_induk, tipe_akun</code></p>
                <input type="file" name="file" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded" accept=".xlsx,.xls" required>
                <p class="text-xs text-gray-400 mt-1">Download template dulu kalau belum punya.</p>
            </div>
            <div class="px-4 py-3 border-t flex items-center">
                <a href="{{ route('akun.template') }}" class="text-sm text-gray-600 hover:text-gray-800 flex items-center gap-1 mr-auto no-underline">
                    <i class="bi bi-download"></i>Download Template
                </a>
                <button type="button" @click="modalImport = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 mr-2">Batal</button>
                <button type="submit" class="px-3 py-1.5 text-sm bg-yellow-500 text-white rounded hover:bg-yellow-600 flex items-center gap-1">
                    <i class="bi bi-upload"></i>Import
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Modal: Detail Akun --}}
<div x-show="modalDetail" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center bg-black/50" style="display:none" @click.self="modalDetail = false">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-lg mx-4" @click.stop>
        <div class="bg-gray-700 text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
            <h6 class="font-semibold flex items-center gap-2 text-sm"><i class="bi bi-eye"></i>Detail Akun</h6>
            <button @click="modalDetail = false" class="text-white hover:text-gray-200">&times;</button>
        </div>
        <div class="p-4 text-sm space-y-2">
            <div x-show="detailLoading" class="flex justify-center py-6">
                <span class="inline-block w-6 h-6 border-2 border-blue-600 border-t-transparent rounded-full animate-spin"></span>
            </div>
            <div x-show="!detailLoading">
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Kode</span><span class="col-span-2 font-mono" x-text="detailData.kode"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Kode Internal</span><span class="col-span-2 font-mono" x-text="detailData.kode_internal"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Nama</span><span class="col-span-2 font-semibold" x-text="detailData.nama"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Tipe Akun</span><span class="col-span-2" x-text="detailData.tipe_akun"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Induk</span><span class="col-span-2" x-text="detailData.induk_nama || '—'"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Level</span><span class="col-span-2" x-text="detailData.level_akun"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Status</span><span class="col-span-2" x-text="detailData.is_aktif ? 'Aktif' : 'Non Aktif'"></span></div>
            <div class="grid grid-cols-3 gap-1"><span class="text-gray-500">Keterangan</span><span class="col-span-2" x-text="detailData.keterangan || '—'"></span></div>
            </div>
        </div>
        <div class="px-4 py-3 border-t flex justify-end">
            <button @click="modalDetail = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Tutup</button>
        </div>
    </div>
</div>

{{-- Modal: Edit Akun --}}
<div x-show="modalEdit" x-transition.opacity class="fixed inset-0 z-40 flex items-center justify-center bg-black/50" style="display:none" @click.self="modalEdit = false">
    <div class="bg-white rounded-lg shadow-xl w-full max-w-md mx-4" @click.stop>
        <div class="bg-indigo-600 text-white px-4 py-3 rounded-t-lg flex justify-between items-center">
            <h6 class="font-semibold flex items-center gap-2 text-sm"><i class="bi bi-pencil"></i>Edit Akun</h6>
            <button @click="modalEdit = false" class="text-white hover:text-gray-200">&times;</button>
        </div>
        <form @submit.prevent="submitEdit()">
            <div x-show="editFetchLoading" class="flex justify-center py-8">
                <span class="inline-block w-6 h-6 border-2 border-indigo-600 border-t-transparent rounded-full animate-spin"></span>
            </div>
            <div x-show="!editFetchLoading" class="p-4 space-y-3">
                <div>
                    <label class="block text-sm font-semibold mb-1">Kode Internal <span class="text-red-500">*</span></label>
                    <input type="text" x-model="editData.kode_internal" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none" required maxlength="30">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Nama Akun <span class="text-red-500">*</span></label>
                    <input type="text" x-model="editData.nama" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none" required maxlength="150">
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Tipe Akun <span class="text-red-500">*</span></label>
                    <select x-model="editData.id_tipe_akun" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none" required>
                        <option value="">-- Pilih Tipe --</option>
                        @foreach($tipeAkuns as $t)
                            <option value="{{ $t->id }}">{{ $t->tipe_akun }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold mb-1">Akun Induk</label>
                    <select x-model="editData.kode_induk" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        <option value="">-- Tidak Ada (akun root) --</option>
                        @foreach($allAkuns as $a)
                            <option value="{{ $a->kode }}">{{ $a->kode_internal }} — {{ $a->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div x-show="!editFetchLoading" class="px-4 py-3 border-t flex justify-end gap-2">
                <button type="button" @click="modalEdit = false" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50">Batal</button>
                <button type="submit" class="px-3 py-1.5 text-sm bg-indigo-600 text-white rounded hover:bg-indigo-700 flex items-center gap-1" :disabled="editLoading">
                    <span x-show="editLoading" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <i x-show="!editLoading" class="bi bi-save"></i>
                    <span x-text="editLoading ? 'Menyimpan...' : 'Simpan'"></span>
                </button>
            </div>
        </form>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
@if($errors->any())
    document.addEventListener('alpine:init', () => {
        setTimeout(() => {
            const el = document.querySelector('[x-data]');
            if (el && el._x_dataStack) el._x_dataStack[0].modalCreate = true;
        }, 100);
    });
@endif

function coaApp() {
    return {
        modalCreate: false,
        modalImport: false,
        modalDetail: false,
        modalEdit: false,
        detailData: {},
        editData: {},
        editLoading: false,
        detailLoading: false,
        editFetchLoading: false,

        init() {
            // Expose functions globally for component buttons
            window.coaOpenDetail = (kode) => this.openDetail(kode);
            window.coaOpenEdit = (kode) => this.openEdit(kode);
            window.coaToggleRow = (kode) => this.toggleRow(kode);

            // Toggle aktif handler
            document.querySelectorAll('.toggle-aktif').forEach(chk => {
                chk.addEventListener('change', (e) => this.handleToggle(e.target));
            });
        },

        handleToggle(cb) {
            const url = cb.dataset.url;
            cb.disabled = true;

            fetch(url, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content },
                body: JSON.stringify({ _method: 'PATCH' }),
            })
            .then(r => r.json())
            .then(data => {
                cb.disabled = false;
                if (data.success) {
                    cb.checked = data.is_aktif;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Status berhasil disimpan', type: 'success' } }));
                } else {
                    cb.checked = !cb.checked;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal menyimpan status', type: 'error' } }));
                }
            })
            .catch(() => {
                cb.disabled = false;
                cb.checked = !cb.checked;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Koneksi gagal', type: 'error' } }));
            });
        },

        openDetail(kode) {
            this.detailData = {};
            this.modalDetail = true;
            this.detailLoading = true;
            fetch('/akun/' + kode + '/detail', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.detailData = data;
                    this.detailLoading = false;
                });
        },

        openEdit(kode) {
            this.editData = {};
            this.modalEdit = true;
            this.editFetchLoading = true;
            fetch('/akun/' + kode + '/detail', { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.editData = {
                        kode: data.kode,
                        kode_internal: data.kode_internal,
                        nama: data.nama,
                        id_tipe_akun: String(data.id_tipe_akun),
                        kode_induk: data.kode_induk ? String(data.kode_induk) : '',
                    };
                    this.editFetchLoading = false;
                });
        },

        submitEdit() {
            this.editLoading = true;
            fetch('/akun/' + this.editData.kode, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ _method: 'PUT', ...this.editData }),
            })
            .then(r => r.json())
            .then(data => {
                this.editLoading = false;
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Akun berhasil diupdate', type: 'success' } }));
                    this.modalEdit = false;
                    setTimeout(() => location.reload(), 500);
                } else {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.error || 'Gagal menyimpan', type: 'error' } }));
                }
            })
            .catch(() => {
                this.editLoading = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Koneksi gagal', type: 'error' } }));
            });
        },

        toggleRow(kode) {
            const chevron = document.getElementById('chevron-' + kode);
            const children = document.querySelectorAll('.akun-row[data-parent="' + kode + '"]');
            if (!children.length) return;

            const isOpen = !chevron.classList.contains('collapsed');
            chevron.classList.toggle('collapsed', isOpen);

            children.forEach(row => {
                row.style.display = isOpen ? 'none' : '';
                if (isOpen) {
                    // collapse all descendants too
                    const childKode = row.dataset.kode;
                    const subChevron = document.getElementById('chevron-' + childKode);
                    if (subChevron) subChevron.classList.add('collapsed');
                    document.querySelectorAll('.akun-row[data-parent="' + childKode + '"]').forEach(sub => {
                        sub.style.display = 'none';
                        const subK = sub.dataset.kode;
                        const sc = document.getElementById('chevron-' + subK);
                        if (sc) sc.classList.add('collapsed');
                        document.querySelectorAll('.akun-row[data-parent="' + subK + '"]').forEach(ss => ss.style.display = 'none');
                    });
                }
            });
        }
    };
}
</script>
@endpush
