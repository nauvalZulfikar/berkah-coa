<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>BERKAH TAF — @yield('page-title', 'Pajak, Akuntansi dan Keuangan')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="bg-gray-100 min-h-screen" x-data="{ toast: '', toastType: 'success', showToast: false }" @toast.window="toast = $event.detail.message; toastType = $event.detail.type || 'success'; showToast = true; setTimeout(() => showToast = false, 3000)">

{{-- Toast notification --}}
<div x-show="showToast" x-transition class="fixed top-4 right-4 z-50 px-4 py-3 rounded-lg shadow-lg text-white text-sm"
    :class="toastType === 'success' ? 'bg-green-600' : 'bg-red-600'" style="display:none">
    <span x-text="toast"></span>
</div>

<nav class="bg-blue-600 text-white shadow">
    <div class="max-w-full mx-auto px-4 py-3 flex items-center">
        <a href="{{ route('akun.index') }}" class="font-bold text-lg tracking-wide flex items-center gap-2 text-white no-underline">
            <i class="bi bi-diagram-3-fill"></i>BERKAH TAF
        </a>
    </div>
</nav>
<div class="bg-white border-b shadow-sm">
    <div class="max-w-full mx-auto px-4 flex items-center gap-1">
        <a href="{{ route('akun.index') }}" class="px-3 py-2 text-sm no-underline flex items-center gap-1.5 border-b-2 transition-colors {{ request()->is('akun*') ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <i class="bi bi-diagram-3"></i>COA
        </a>
        <a href="{{ route('mutasi-bank.index') }}" class="px-3 py-2 text-sm no-underline flex items-center gap-1.5 border-b-2 transition-colors {{ request()->is('mutasi-bank*') ? 'border-blue-600 text-blue-600 font-semibold' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
            <i class="bi bi-bank"></i>Mutasi Bank
        </a>
    </div>
</div>

<div class="px-3 md:px-6 py-4">
    @if(session('success'))
        <div class="mb-3 bg-green-100 border border-green-300 text-green-800 px-4 py-3 rounded-lg flex items-center justify-between text-sm">
            <span><i class="bi bi-check-circle mr-2"></i>{{ session('success') }}</span>
            <button onclick="this.parentElement.remove()" class="text-green-800 hover:text-green-900">&times;</button>
        </div>
    @endif
    @if($errors->any())
        <div class="mb-3 bg-red-100 border border-red-300 text-red-800 px-4 py-3 rounded-lg text-sm">
            <i class="bi bi-exclamation-triangle mr-2"></i>
            <ul class="list-disc list-inside">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    @yield('content')
</div>

@stack('scripts')
</body>
</html>
