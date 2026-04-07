@extends('layouts.app')
@section('page-title', 'Detail Batch #' . $batch->id)

@section('content')
<div x-data="mutasiDetailApp()" x-init="init()">

{{-- Header --}}
<div class="flex items-center gap-2 mb-3">
    <a href="{{ route('mutasi-bank.index') }}" class="text-gray-500 hover:text-blue-600 no-underline"><i class="bi bi-arrow-left"></i></a>
    <h4 class="text-xl font-bold flex items-center gap-2"><i class="bi bi-receipt"></i>Detail Batch Impor #{{ $batch->id }}</h4>
</div>

{{-- Batch Info --}}
<div class="bg-white rounded-lg shadow-sm mb-3">
    <div class="p-4">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
            <div>
                <span class="text-gray-500 block text-xs">File</span>
                <span class="font-semibold">{{ $batch->nama_file_asli }}</span>
            </div>
            <div>
                <span class="text-gray-500 block text-xs">Rekening</span>
                <span class="font-mono">{{ $batch->rekening->no_rekening ?? '-' }}</span>
                <span class="text-gray-400 text-xs block">{{ $batch->rekening->nama_pemilik ?? '' }}</span>
            </div>
            <div>
                <span class="text-gray-500 block text-xs">Tanggal Impor</span>
                <span>{{ $batch->waktu_ubah?->format('d M Y H:i') }}</span>
            </div>
            <div>
                <span class="text-gray-500 block text-xs">Status</span>
                <span class="px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ $batch->status_impor === 'selesai' ? 'bg-green-100 text-green-700' : ($batch->status_impor === 'gagal' ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-700') }}">
                    {{ $batch->status_impor }}
                </span>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-3 mt-3 text-sm">
            <div class="text-center p-2 bg-green-50 rounded border-t-2 border-green-400">
                <div class="text-lg font-bold text-green-600">{{ $batch->jumlah_baris_valid }}</div>
                <div class="text-xs text-gray-500">Valid</div>
            </div>
            <div class="text-center p-2 bg-yellow-50 rounded border-t-2 border-yellow-400">
                <div class="text-lg font-bold text-yellow-600">{{ $batch->jumlah_duplikat }}</div>
                <div class="text-xs text-gray-500">Duplikat</div>
            </div>
            <div class="text-center p-2 bg-blue-50 rounded border-t-2 border-blue-400">
                <div class="text-lg font-bold text-blue-600">{{ $batch->jumlah_baris_csv }}</div>
                <div class="text-xs text-gray-500">Total Baris CSV</div>
            </div>
        </div>
        <div class="mt-3">
            <a href="{{ route('mutasi-bank.download-file', $batch->id) }}"
                onclick="this.innerHTML='<span class=\'inline-block w-4 h-4 border-2 border-gray-500 border-t-transparent rounded-full animate-spin mr-1\'></span>Downloading...'; setTimeout(() => { this.innerHTML='<i class=\'bi bi-download\'></i> Download File Arsip'; }, 3000)"
                class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 inline-flex items-center gap-1 no-underline text-gray-700">
                <i class="bi bi-download"></i> Download File Arsip
            </a>
        </div>
    </div>
</div>

{{-- Filter --}}
<div class="bg-white rounded-lg shadow-sm mb-3">
    <div class="p-3">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-5 gap-2">
            <div>
                <label class="text-xs text-gray-400 block mb-0.5">Cari keterangan</label>
                <input type="text" x-model="filters.cari" @keyup.enter="applyFilter()" placeholder="Ketik lalu Enter..."
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-0.5">Tanggal dari</label>
                <input type="date" x-model="filters.tgl_dari" @change="applyFilter()"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-0.5">Tanggal sampai</label>
                <input type="date" x-model="filters.tgl_sampai" @change="applyFilter()"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
            </div>
            <div>
                <label class="text-xs text-gray-400 block mb-0.5">Arah mutasi</label>
                <select x-model="filters.arah" @change="applyFilter()"
                    class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua (CR/DB)</option>
                    <option value="CR">CR (Kredit masuk)</option>
                    <option value="DB">DB (Debit keluar)</option>
                </select>
            </div>
            <div class="flex gap-1 items-end">
                <button @click="applyFilter()" :disabled="loading" class="flex-1 px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 flex items-center justify-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
                    <span x-show="loading" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                    <i x-show="!loading" class="bi bi-search"></i>
                    <span x-text="loading ? 'Memuat...' : 'Cari'"></span>
                </button>
                <button @click="resetFilter()" :disabled="loading" class="px-3 py-1.5 text-sm border border-gray-300 rounded hover:bg-gray-50 disabled:opacity-50 transition-all">Reset</button>
            </div>
        </div>
    </div>
</div>

{{-- Summary Totals (computed from current page data) --}}
<div x-show="!loading && rows.length > 0" class="grid grid-cols-2 gap-3 mb-3">
    <div class="bg-green-50 rounded-lg p-3 flex items-center justify-between border border-green-200">
        <div class="flex items-center gap-2">
            <span class="px-1.5 py-0.5 rounded text-xs font-bold bg-green-100 text-green-700">CR</span>
            <span class="text-sm text-green-800">Total Kredit Masuk</span>
        </div>
        <span class="font-mono font-bold text-green-700" x-text="formatNumber(totalCR)"></span>
    </div>
    <div class="bg-red-50 rounded-lg p-3 flex items-center justify-between border border-red-200">
        <div class="flex items-center gap-2">
            <span class="px-1.5 py-0.5 rounded text-xs font-bold bg-red-100 text-red-700">DB</span>
            <span class="text-sm text-red-800">Total Debit Keluar</span>
        </div>
        <span class="font-mono font-bold text-red-700" x-text="formatNumber(totalDB)"></span>
    </div>
</div>

{{-- Staging Table --}}
<div class="bg-white rounded-lg shadow-sm">
    <div class="overflow-auto" style="max-height:55vh;">
        <table class="w-full text-sm" style="min-width:700px;">
            <thead class="bg-gray-50 sticky top-0 z-10">
                <tr>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600" style="width:50px">#</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600" style="width:100px">Tanggal</th>
                    <th class="px-3 py-2 text-left font-semibold text-gray-600">Keterangan</th>
                    <th class="px-3 py-2 text-center font-semibold text-gray-600" style="width:50px">D/K</th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-600" style="width:130px">Jumlah</th>
                    <th class="px-3 py-2 text-right font-semibold text-gray-600" style="width:140px">Saldo</th>
                </tr>
            </thead>
            <tbody>
                <tr x-show="loading">
                    <td colspan="6" class="text-center py-8 text-gray-400">
                        <span class="inline-block w-5 h-5 border-2 border-blue-600 border-t-transparent rounded-full animate-spin mr-2"></span>Memuat...
                    </td>
                </tr>
                <tr x-show="!loading && rows.length === 0">
                    <td colspan="6" class="text-center text-gray-400 py-8">
                        <i class="bi bi-search text-2xl block mb-2"></i>Tidak ada data mutasi yang cocok.
                    </td>
                </tr>
                <template x-for="(row, i) in rows" :key="row.id">
                    <tr class="border-t hover:bg-gray-50">
                        <td class="px-3 py-1.5 text-gray-400" x-text="((pagination.current_page - 1) * pagination.per_page) + i + 1"></td>
                        <td class="px-3 py-1.5 whitespace-nowrap" x-text="formatDate(row.tanggal)"></td>
                        <td class="px-3 py-1.5">
                            <span class="block truncate max-w-[350px]" x-text="row.keterangan" :title="row.keterangan"></span>
                        </td>
                        <td class="px-3 py-1.5 text-center">
                            <span class="px-1.5 py-0.5 rounded text-xs font-bold"
                                :class="row.arah === 'CR' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'"
                                x-text="row.arah"></span>
                        </td>
                        <td class="px-3 py-1.5 text-right font-mono" :class="row.arah === 'CR' ? 'text-green-700' : 'text-red-700'" x-text="formatNumber(row.jumlah)"></td>
                        <td class="px-3 py-1.5 text-right font-mono text-gray-500" x-text="formatNumber(row.saldo)"></td>
                    </tr>
                </template>
            </tbody>
            {{-- Footer totals --}}
            <tfoot x-show="!loading && rows.length > 0">
                <tr class="border-t-2 border-gray-300 bg-gray-50 font-semibold">
                    <td colspan="4" class="px-3 py-2 text-right text-gray-600 text-xs uppercase tracking-wide">Total halaman ini</td>
                    <td class="px-3 py-2 text-right font-mono text-sm">
                        <span class="text-green-700" x-text="'CR ' + formatNumber(pageCR)"></span>
                        <br>
                        <span class="text-red-700" x-text="'DB ' + formatNumber(pageDB)"></span>
                    </td>
                    <td class="px-3 py-2"></td>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- Pagination --}}
    <div x-show="pagination.last_page > 1" class="px-3 py-2 border-t flex items-center justify-between text-sm text-gray-500">
        <span>Hal <span x-text="pagination.current_page"></span> dari <span x-text="pagination.last_page"></span> (<span x-text="pagination.total"></span> transaksi)</span>
        <div class="flex gap-1">
            <button @click="loadData(pagination.current_page - 1)" :disabled="pagination.current_page <= 1"
                class="px-2 py-1 border rounded hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">&laquo;</button>
            <template x-for="p in pageNumbers()" :key="p">
                <button @click="loadData(p)" class="px-2 py-1 border rounded hover:bg-gray-50"
                    :class="p === pagination.current_page ? 'bg-blue-600 text-white border-blue-600' : ''" x-text="p"></button>
            </template>
            <button @click="loadData(pagination.current_page + 1)" :disabled="pagination.current_page >= pagination.last_page"
                class="px-2 py-1 border rounded hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">&raquo;</button>
        </div>
    </div>
    <div x-show="!loading && rows.length > 0" class="px-3 py-2 text-gray-400 text-xs border-t">
        Total: <span x-text="pagination.total"></span> transaksi
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
function mutasiDetailApp() {
    const batchId = {{ $batch->id }};
    return {
        rows: [],
        loading: false,
        pagination: { current_page: 1, last_page: 1, total: 0, per_page: 25 },
        filters: { cari: '', tgl_dari: '', tgl_sampai: '', arah: '' },
        totalCR: 0,
        totalDB: 0,

        get pageCR() {
            return this.rows.filter(r => r.arah === 'CR').reduce((s, r) => s + Number(r.jumlah), 0);
        },
        get pageDB() {
            return this.rows.filter(r => r.arah === 'DB').reduce((s, r) => s + Number(r.jumlah), 0);
        },

        init() { this.loadData(); },

        applyFilter() { this.loadData(1); },

        resetFilter() {
            this.filters = { cari: '', tgl_dari: '', tgl_sampai: '', arah: '' };
            this.loadData(1);
        },

        loadData(page = 1) {
            this.loading = true;
            const params = new URLSearchParams({ page, per_page: 25 });
            if (this.filters.cari) params.append('cari', this.filters.cari);
            if (this.filters.tgl_dari) params.append('tgl_dari', this.filters.tgl_dari);
            if (this.filters.tgl_sampai) params.append('tgl_sampai', this.filters.tgl_sampai);
            if (this.filters.arah) params.append('arah', this.filters.arah);

            fetch('/mutasi-bank/' + batchId + '/data?' + params, { headers: { 'Accept': 'application/json' } })
                .then(r => r.json())
                .then(data => {
                    this.rows = data.data;
                    this.pagination = { current_page: data.current_page, last_page: data.last_page, total: data.total, per_page: data.per_page };
                    this.loading = false;
                })
                .catch(() => {
                    this.loading = false;
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Gagal memuat data', type: 'error' } }));
                });
        },

        pageNumbers() {
            const c = this.pagination.current_page;
            const l = this.pagination.last_page;
            const pages = [];
            let start = Math.max(1, c - 2);
            let end = Math.min(l, c + 2);
            for (let i = start; i <= end; i++) pages.push(i);
            return pages;
        },

        formatDate(dt) {
            if (!dt) return '-';
            const d = new Date(dt);
            return d.toLocaleDateString('id-ID', { day: '2-digit', month: 'short', year: 'numeric' });
        },

        formatNumber(val) {
            if (val === null || val === undefined) return '-';
            return Number(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endpush
