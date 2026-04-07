@extends('layouts.app')
@section('page-title', 'Import CSV Mutasi Bank')

@push('styles')
<style>
@keyframes checkScale { 0% { transform: scale(0); } 60% { transform: scale(1.2); } 100% { transform: scale(1); } }
.check-animate { animation: checkScale 0.5s ease-out; }
</style>
@endpush

@section('content')
<div x-data="mutasiUploadApp()">

{{-- Header --}}
<div class="flex items-center gap-2 mb-3">
    <a href="{{ route('mutasi-bank.index') }}" class="text-gray-500 hover:text-blue-600 no-underline"><i class="bi bi-arrow-left"></i></a>
    <h4 class="text-xl font-bold flex items-center gap-2"><i class="bi bi-file-earmark-arrow-up"></i>Import CSV Mutasi Bank</h4>
</div>

{{-- Step Indicator --}}
<div class="flex items-center justify-center gap-0 mb-4">
    <template x-for="(s, i) in [{n:1,label:'Upload'},{n:2,label:'Preview'},{n:3,label:'Selesai'}]" :key="s.n">
        <div class="flex items-center">
            <div class="flex flex-col items-center">
                <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold transition-all duration-300"
                    :class="step >= s.n ? 'bg-blue-600 text-white shadow' : 'bg-gray-200 text-gray-500'">
                    <i x-show="step > s.n" class="bi bi-check-lg"></i>
                    <span x-show="step <= s.n" x-text="s.n"></span>
                </div>
                <span class="text-xs mt-1 transition-colors" :class="step >= s.n ? 'text-blue-600 font-semibold' : 'text-gray-400'" x-text="s.label"></span>
            </div>
            <div x-show="i < 2" class="w-12 sm:w-20 h-0.5 mx-1 mb-4 transition-colors duration-300" :class="step > s.n ? 'bg-blue-600' : 'bg-gray-200'"></div>
        </div>
    </template>
</div>

{{-- STEP 1: Upload --}}
<div x-show="step === 1" x-transition class="bg-white rounded-lg shadow-sm">
    <div class="p-4 space-y-4">
        <div>
            <label class="block text-sm font-semibold mb-1">Rekening Bank <span class="text-red-500">*</span></label>
            <select x-model="idRekening" class="w-full px-3 py-1.5 text-sm border border-gray-300 rounded focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <option value="">-- Pilih Rekening --</option>
                @foreach($rekenings as $r)
                    <option value="{{ $r->id }}">{{ $r->bank->kode_internal ?? '' }} — {{ $r->no_rekening }} ({{ $r->nama_pemilik }})</option>
                @endforeach
            </select>
            <p class="text-xs text-gray-400 mt-1">Rekening tujuan impor. Harus sesuai dengan isi file CSV.</p>
        </div>

        <div>
            <label class="block text-sm font-semibold mb-1">File CSV <span class="text-red-500">*</span></label>
            <input type="file" x-ref="csvFile" accept=".csv,.CSV,.txt" class="hidden"
                @change="fileName = $refs.csvFile.files[0]?.name || ''">

            <div class="border-2 border-dashed rounded-lg p-6 text-center transition-all duration-200"
                :class="dragging ? 'border-blue-500 bg-blue-50 scale-[1.02] shadow-lg' : (fileName ? 'border-green-400 bg-green-50' : 'border-gray-300 hover:border-blue-400')"
                @dragover.prevent="dragging = true"
                @dragleave.prevent="dragging = false"
                @dragend.prevent="dragging = false"
                @drop.prevent="dragging = false; $refs.csvFile.files = $event.dataTransfer.files; fileName = $event.dataTransfer.files[0]?.name">

                <div x-show="!fileName">
                    <i class="bi bi-cloud-arrow-up text-3xl" :class="dragging ? 'text-blue-500' : 'text-gray-400'"></i>
                    <p class="text-sm mt-2" :class="dragging ? 'text-blue-600 font-semibold' : 'text-gray-500'"
                        x-text="dragging ? 'Lepaskan file di sini...' : 'Drag & drop file CSV di sini, atau'"></p>
                    <button x-show="!dragging" type="button" @click="$refs.csvFile.click()"
                        class="mt-2 px-3 py-1.5 text-sm bg-blue-600 text-white rounded hover:bg-blue-700 cursor-pointer inline-flex items-center gap-1">
                        <i class="bi bi-folder2-open"></i> Pilih File
                    </button>
                    <p x-show="!dragging" class="text-xs text-gray-400 mt-2">Format: CSV dari KlikBCA Bisnis. Maks 5MB.</p>
                </div>

                <div x-show="fileName">
                    <i class="bi bi-file-earmark-check text-3xl text-green-500"></i>
                    <p class="text-sm text-green-700 font-semibold mt-2" x-text="fileName"></p>
                    <button type="button" @click="fileName = ''; $refs.csvFile.value = ''"
                        class="mt-2 text-xs text-red-500 hover:text-red-700 inline-flex items-center gap-1">
                        <i class="bi bi-x-circle"></i> Ganti file
                    </button>
                </div>
            </div>
        </div>

        <div x-show="errorMsg" x-transition class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg flex items-start gap-2 text-sm">
            <i class="bi bi-exclamation-triangle-fill text-red-500 mt-0.5"></i>
            <span x-text="errorMsg"></span>
        </div>

        <div class="flex justify-end">
            <button @click="uploadCsv()" :disabled="uploading"
                class="px-4 py-2 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
                <span x-show="uploading" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                <i x-show="!uploading" class="bi bi-eye"></i>
                <span x-text="uploading ? 'Memproses file...' : 'Upload & Preview'"></span>
            </button>
        </div>
    </div>
</div>

{{-- STEP 2: Preview --}}
<div x-show="step === 2" x-transition style="display:none">
    {{-- CSV Info Card --}}
    <div class="bg-white rounded-lg shadow-sm mb-3">
        <div class="p-4">
            <h5 class="font-semibold text-sm mb-2 flex items-center gap-1"><i class="bi bi-info-circle text-blue-500"></i>Informasi File</h5>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 text-sm">
                <div><span class="text-gray-500 block text-xs">File</span><span class="font-semibold truncate block" :title="previewData.file_name" x-text="previewData.file_name"></span></div>
                <div><span class="text-gray-500 block text-xs">No. Rekening</span><span class="font-mono" x-text="previewData.csv_info?.no_rek || '-'"></span></div>
                <div><span class="text-gray-500 block text-xs">Nama</span><span x-text="previewData.csv_info?.nama || '-'"></span></div>
                <div><span class="text-gray-500 block text-xs">Mata Uang</span><span x-text="previewData.csv_info?.mata_uang || '-'"></span></div>
            </div>
        </div>
    </div>

    {{-- Stats --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mb-3">
        <div class="bg-white rounded-lg shadow-sm p-3 text-center border-t-2 border-blue-500">
            <div class="text-2xl font-bold text-blue-600" x-text="previewData.total_rows || 0"></div>
            <div class="text-xs text-gray-500">Total Baris</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-3 text-center border-t-2 border-green-500">
            <div class="text-2xl font-bold text-green-600" x-text="(previewData.total_rows || 0) - (previewData.duplicates_count || 0)"></div>
            <div class="text-xs text-gray-500">Akan Diimpor</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-3 text-center border-t-2 border-yellow-500">
            <div class="text-2xl font-bold text-yellow-600" x-text="previewData.duplicates_count || 0"></div>
            <div class="text-xs text-gray-500">Duplikat (skip)</div>
        </div>
        <div class="bg-white rounded-lg shadow-sm p-3 text-center border-t-2 border-gray-400">
            <div class="text-2xl font-bold text-gray-600" x-text="previewData.errors?.length || 0"></div>
            <div class="text-xs text-gray-500">Error</div>
        </div>
    </div>

    {{-- Preview Table --}}
    <div class="bg-white rounded-lg shadow-sm mb-3">
        <div class="px-4 py-2 border-b flex items-center justify-between">
            <h5 class="font-semibold text-sm flex items-center gap-1"><i class="bi bi-table text-blue-500"></i> Preview (maks 50 baris)</h5>
            <span class="text-xs text-gray-400" x-text="(previewData.preview?.length || 0) + ' baris ditampilkan'"></span>
        </div>
        <div class="overflow-auto" style="max-height:50vh;">
            <table class="w-full text-sm" style="min-width:650px;">
                <thead class="bg-gray-50 sticky top-0">
                    <tr>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600" style="width:40px">#</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600" style="width:95px">Tanggal</th>
                        <th class="px-3 py-2 text-left font-semibold text-gray-600">Keterangan</th>
                        <th class="px-3 py-2 text-center font-semibold text-gray-600" style="width:50px">D/K</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600" style="width:120px">Jumlah</th>
                        <th class="px-3 py-2 text-right font-semibold text-gray-600" style="width:130px">Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="(row, i) in previewData.preview || []" :key="i">
                        <tr class="border-t hover:bg-gray-50">
                            <td class="px-3 py-1.5 text-gray-400" x-text="row.baris_csv"></td>
                            <td class="px-3 py-1.5 whitespace-nowrap" x-text="row.tanggal"></td>
                            <td class="px-3 py-1.5">
                                <span class="block truncate max-w-[300px]" x-text="row.keterangan" :title="row.keterangan"></span>
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
            </table>
        </div>
    </div>

    {{-- Actions --}}
    <div class="flex justify-end gap-2">
        <button @click="reset()" :disabled="confirming"
            class="px-4 py-2 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 flex items-center gap-1 disabled:opacity-50 disabled:cursor-not-allowed transition-all">
            <i class="bi bi-x-lg"></i> Batal
        </button>
        <button @click="confirmImport()" :disabled="confirming"
            class="px-4 py-2 text-sm bg-green-600 text-white rounded-lg hover:bg-green-700 flex items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all shadow-sm">
            <span x-show="confirming" class="inline-block w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
            <i x-show="!confirming" class="bi bi-check-circle"></i>
            <span x-text="confirming ? 'Mengimpor data...' : 'Konfirmasi Import'"></span>
        </button>
    </div>
</div>

{{-- STEP 3: Success --}}
<div x-show="step === 3" x-transition style="display:none">
    <div class="bg-white rounded-lg shadow-sm p-8 text-center">
        <div class="check-animate inline-flex items-center justify-center w-20 h-20 rounded-full bg-green-100 mb-4">
            <i class="bi bi-check-lg text-4xl text-green-600"></i>
        </div>
        <h4 class="text-xl font-bold text-gray-800">Import Berhasil!</h4>
        <p class="text-sm text-gray-500 mt-2">
            <span class="font-bold text-green-600 text-lg" x-text="batchResult.imported"></span> transaksi berhasil diimpor
        </p>
        <p x-show="batchResult.duplicates > 0" class="text-sm text-yellow-600 mt-1">
            <i class="bi bi-info-circle"></i> <span x-text="batchResult.duplicates"></span> transaksi duplikat dilewati
        </p>
        <div class="mt-6 flex justify-center gap-3">
            <a :href="'/mutasi-bank/' + batchResult.batch_id" class="px-5 py-2.5 text-sm bg-blue-600 text-white rounded-lg hover:bg-blue-700 no-underline flex items-center gap-2 shadow-sm">
                <i class="bi bi-eye"></i> Lihat Detail
            </a>
            <a href="{{ route('mutasi-bank.create') }}" @click.prevent="reset()" class="px-5 py-2.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 no-underline text-gray-700 flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Import Lagi
            </a>
            <a href="{{ route('mutasi-bank.index') }}" class="px-5 py-2.5 text-sm border border-gray-300 rounded-lg hover:bg-gray-50 no-underline text-gray-700 flex items-center gap-2">
                <i class="bi bi-list-ul"></i> Daftar Batch
            </a>
        </div>
    </div>
</div>

</div>
@endsection

@push('scripts')
<script>
function mutasiUploadApp() {
    return {
        step: 1,
        idRekening: '',
        fileName: '',
        dragging: false,
        uploading: false,
        confirming: false,
        errorMsg: '',
        previewData: {},
        batchResult: {},

        uploadCsv() {
            this.errorMsg = '';
            const fileInput = this.$refs.csvFile;
            if (!fileInput || !fileInput.files.length) {
                this.errorMsg = 'Pilih file CSV terlebih dahulu.';
                return;
            }
            if (!this.idRekening) {
                this.errorMsg = 'Pilih rekening bank terlebih dahulu.';
                return;
            }

            this.uploading = true;
            const formData = new FormData();
            formData.append('file', fileInput.files[0]);
            formData.append('id_rekening', this.idRekening);

            fetch('{{ route("mutasi-bank.upload") }}', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: formData,
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                this.uploading = false;
                if (!ok || !data.success) {
                    this.errorMsg = data.message || 'Gagal memproses file.';
                    if (data.errors && data.errors.length) this.errorMsg += ' ' + data.errors.join('; ');
                    return;
                }
                this.previewData = data;
                this.step = 2;
            })
            .catch((err) => {
                this.uploading = false;
                this.errorMsg = 'Koneksi gagal. Silakan coba lagi. (' + err.message + ')';
            });
        },

        confirmImport() {
            this.confirming = true;
            fetch('{{ route("mutasi-bank.store") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({
                    file_temp: this.previewData.file_temp,
                    id_rekening: this.idRekening,
                    file_name: this.previewData.file_name,
                }),
            })
            .then(r => r.json().then(data => ({ ok: r.ok, data })))
            .then(({ ok, data }) => {
                this.confirming = false;
                if (!ok || !data.success) {
                    window.dispatchEvent(new CustomEvent('toast', { detail: { message: data.message || 'Gagal mengimpor', type: 'error' } }));
                    return;
                }
                this.batchResult = data;
                this.step = 3;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Import berhasil!', type: 'success' } }));
            })
            .catch(() => {
                this.confirming = false;
                window.dispatchEvent(new CustomEvent('toast', { detail: { message: 'Koneksi gagal', type: 'error' } }));
            });
        },

        reset() {
            this.step = 1;
            this.previewData = {};
            this.batchResult = {};
            this.fileName = '';
            this.errorMsg = '';
            if (this.$refs.csvFile) this.$refs.csvFile.value = '';
        },

        formatNumber(val) {
            if (val === null || val === undefined) return '-';
            return Number(val).toLocaleString('id-ID', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }
    };
}
</script>
@endpush
