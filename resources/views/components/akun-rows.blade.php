@foreach($nodes as $akun)
@php $children = $allAkuns->where('kode_induk', $akun->kode)->values(); @endphp
<tr class="akun-row hover:bg-gray-50"
    data-kode="{{ $akun->kode }}"
    data-parent="{{ $akun->kode_induk ?? '' }}"
    data-depth="{{ $depth }}">
    <td class="px-3 py-2 text-gray-500 text-xs font-mono">{{ $akun->kode_internal }}</td>
    <td class="px-3 py-2 cursor-pointer" style="padding-left: {{ ($depth * 1.5) + 0.75 }}rem"
        onclick="window.coaToggleRow({{ $akun->kode }})">
        @if($children->count())
        <i id="chevron-{{ $akun->kode }}" class="bi bi-chevron-right mr-1 text-gray-400 text-xs chevron-icon"></i>
        @else
        <span style="display:inline-block; width:1rem"></span>
        @endif
        {{ $akun->nama }}
    </td>
    <td class="px-3 py-2 text-gray-500">{{ $akun->induk?->kode_internal ?? '—' }}</td>
    <td class="px-3 py-2">
        <span class="inline-block px-2 py-0.5 text-xs rounded bg-gray-200 text-gray-700">{{ $akun->tipeAkun?->tipe_akun }}</span>
    </td>
    <td class="px-3 py-2 text-center">
        <div class="flex items-center justify-center gap-2">
            <input class="toggle-switch toggle-aktif" type="checkbox"
                data-kode="{{ $akun->kode }}"
                data-url="{{ route('akun.toggleAktif', $akun->kode) }}"
                {{ $akun->is_aktif ? 'checked' : '' }}>
            <button onclick="window.coaOpenDetail({{ $akun->kode }})"
                class="text-blue-500 hover:text-blue-700 p-1" title="Detail">
                <i class="bi bi-eye"></i>
            </button>
            <button onclick="window.coaOpenEdit({{ $akun->kode }})"
                class="text-gray-500 hover:text-indigo-600 p-1" title="Edit">
                <i class="bi bi-pencil"></i>
            </button>
        </div>
    </td>
</tr>
@if($children->count())
<x-akun-rows :nodes="$children" :allAkuns="$allAkuns" :depth="$depth + 1" />
@endif
@endforeach
