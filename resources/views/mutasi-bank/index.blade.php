@extends('layouts.app')
@section('page-title', 'Mutasi Bank')

@section('content')
<div x-data="mutasiBatchApp()" x-init="init()">

{{-- Header --}}
<div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 mb-3">
    <h4 class="text-xl font-bold flex items-center gap-2"><i class="bi bi-bank"></i>Mutasi Bank — Daftar Impor</h4>
    <a href="{{ route('mutasi-bank.create') }}" class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 no-underline shadow-sm hover:shadow transition-all">
        <i class="bi bi-file-earmark-arrow-up text-base"></i>Import CSV Baru
    </a>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg shadow-sm mb-3">
    <div class="p-3">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
            <select x-model="filters.id_rekening" @change="applyFilter()" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Semua Rekening --</option>
                @foreach($rekenings as $r)
                    <option value="{{ $r->id }}">{{ $r->no_rekening }} — {{ $r->nama_pemilik }}</option>
                @endforeach
            </select>
            <select x-model="filters.status" @change="applyFilter()" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Semua Status --</option>
                <option value="selesai">Selesai</option>
                <option value="gagal">Gagal</option>
                <option value="pending">Pending</option>
            </select>
            <button @click="loadBatches()" :disabled="loading" class="px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                <span x-show="loading" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <i x-show="!loading" class="bi bi-arrow-clockwise"></i>
                <span x-text="loading ? 'Memuat...' : 'Refresh'"></span>
            </button>
        </div>
    </div>
</div>

{{-- Empty State --}}
<div x-show="!loading && batches.length === 0" class="bg-white rounded-lg shadow-sm p-8 text-center">
    <div class="inline-flex items-center justify-center w-16 h-16 rounded-full bg-blue-50 mb-4">
        <i class="bi bi-inbox text-3xl text-blue-400"></i>
    </div>
    <h5 class="text-lg font-semibold text-gray-700">Belum ada data impor</h5>
    <p class="text-sm text-gray-400 mt-1 mb-4">Upload file CSV mutasi bank untuk memulai.</p>
    <a href="{{ route('mutasi-bank.create') }}" class="inline-flex items-center gap-2 px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 no-underline shadow-sm">
        <i class="bi bi-file-earmark-arrow-up"></i>Import CSV Pertama
    </a>
</div>

{{-- Table --}}
<div x-show="loading || batches.length > 0" class="bg-white rounded-lg shadow-sm">
    <div class="overflow-auto" style="max-height:65vh;">
        <table class="w-full text-sm" style="min-width:700px;">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600" style="width:50px">#</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Tanggal Impor</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">File</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Rekening</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600">Valid</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600">Duplikat</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600">Status</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600" style="width:100px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <tr x-show="loading">
                    <td colspan="8" class="text-center py-8 text-gray-400">
                        <span class="inline-block w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mr-2"></span>Memuat...
                    </td>
                </tr>
                <template x-for="(b, i) in batches" :key="b.id">
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-3 py-2 text-gray-500" x-text="pagination.from + i"></td>
                        <td class="px-3 py-2 whitespace-nowrap" x-text="formatDate(b.waktu_ubah)"></td>
                        <td class="px-3 py-2 max-w-[200px]">
                            <a :href="'/mutasi-bank/' + b.id" class="text-blue-600 hover:underline block truncate" x-text="b.nama_file_asli" :title="b.nama_file_asli"></a>
                        </td>
                        <td class="px-3 py-2 whitespace-nowrap">
                            <span class="font-mono text-xs" x-text="b.rekening ? b.rekening.no_rekening : '-'"></span>
                            <span class="text-gray-400 text-xs block" x-text="b.rekening ? b.rekening.nama_pemilik : ''"></span>
                        </td>
                        <td class="px-3 py-2 text-center font-semibold text-green-600" x-text="b.jumlah_baris_valid"></td>
                        <td class="px-3 py-2 text-center font-semibold" :class="b.jumlah_duplikat > 0 ? 'text-yellow-600' : 'text-gray-400'" x-text="b.jumlah_duplikat"></td>
                        <td class="px-3 py-2 text-center">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold"
                                :class="b.status_impor === 'selesai' ? 'bg-green-100 text-green-700' : b.status_impor === 'gagal' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700'"
                                x-text="b.status_impor"></span>
                        </td>
                        <td class="px-3 py-2 text-center">
                            <button @click="deleteBatch(b.id)" :disabled="deleting === b.id" class="text-red-500 hover:text-red-700 disabled:opacity-50" title="Hapus">
                                <span x-show="deleting === b.id" class="inline-block w-4 h-4 border-2 border-red-500 border-t-transparent rounded-full animate-spin"></span>
                                <i x-show="deleting !== b.id" class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    <div x-show="pagination.last_page > 1" class="px-3 py-2 border-t flex items-center justify-between text-sm text-gray-500">
        <span>Hal <span x-text="pagination.current_page"></span> dari <span x-text="pagination.last_page"></span> (<span x-text="pagination.total"></span> data)</span>
        <div class="flex gap-1">
            <button @click="loadBatches(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                class="px-2 py-1 border rounded hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">&laquo;</button>
            <button @click="loadBatches(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
                class="px-2 py-1 border rounded hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">&raquo;</button>
        </div>
    </div>
    <div x-show="batches.length > 0" class="px-3 py-2 text-gray-400 text-xs border-t">Total: <span x-text="pagination.total"></span> batch</div>
</div>

</div>
@endsection

@push('scripts')
<script>
function mutasiBatchApp() {
    return {
        batches: [],
        loading: false,
        deleting: null,
        pagination: { current_page: 1, last_page: 1, total: 0, from: 1 },
        filters: { id_rekening: '', status: '' },

        init() { this.loadBatches(); },

        applyFilter() { this.loadBatches(1); },

        loadBatches(page = 1) {
            this.loading = true;
            const params = new URLSearchParams({ page, per_page: 15 });
            if (this.filters.id_rekening) params.append('id_rekening', this.filters.id_rekening);
            if (this.filters.status) params.append('status', this.filters.status);

            fetch('/mutasi-bank/list?' + params, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.batches = data.data;
                    this.pagination = { current_page: data.current_page, last_page: data.last_page, total: data.total, from: data.from || 1 };
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal memuat data', type: 'error' } }));
                });
        },

        deleteBatch(id) {
            if (!confirm('Hapus batch ini beserta seluruh data mutasinya?')) return;
            this.deleting = id;
            fetch('/mutasi-bank/' + id, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content, 'Accept': 'application/json' },
            })
            .then(r => r.json())
            .then(data => {
                this.deleting = null;
                if (data.success) {
                    this.batches = this.batches.filter(b => b.id !== id);
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Batch berhasil dihapus', type: 'success' } }));
                }
            })
            .catch(() => {
                this.deleting = null;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal menghapus', type: 'error' } }));
            });
        },

        formatDate(dt) {
            if (!dt) return '-';
            const d = new Date(dt);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
        }
    };
}
</script>
@endpush
