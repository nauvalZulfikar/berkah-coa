<ul class="list-unstyled ps-3">
    @foreach($nodes as $node)
    <li class="tree-item">
        <div class="d-flex align-items-center gap-2 py-1">
            @if(count($node->children) > 0)
                <span class="tree-toggle text-muted" onclick="toggleTree(this)">
                    <i class="bi bi-caret-down-fill"></i>
                </span>
            @else
                <span style="width:16px"></span>
            @endif
            <a href="{{ route('akun.show', $node->kode) }}" class="text-decoration-none">
                <code class="text-primary">{{ $node->kode_internal }}</code>
            </a>
            <span>{{ $node->nama }}</span>
            <span class="badge bg-secondary badge-tipe">{{ $node->tipeAkun?->tipe_akun }}</span>
            @if(!$node->is_aktif)
                <span class="badge bg-danger badge-tipe">Non Aktif</span>
            @endif
        </div>
        @if(count($node->children) > 0)
            <div class="tree-children">
                @include('akun._tree', ['nodes' => $node->children])
            </div>
        @endif
    </li>
    @endforeach
</ul>

@pushOnce('scripts')
<script>
function toggleTree(el) {
    const li = el.closest('li');
    const children = li.querySelector('.tree-children');
    const icon = el.querySelector('i');
    if (children.style.display === 'none') {
        children.style.display = '';
        icon.className = 'bi bi-caret-down-fill';
    } else {
        children.style.display = 'none';
        icon.className = 'bi bi-caret-right-fill';
    }
}
</script>
@endPushOnce
