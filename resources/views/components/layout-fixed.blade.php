@props(['title' => 'Judul Halaman'])

{{-- 
    COMPONENT: LAYOUT FIXED (1 SCREEN NO SCROLL)
    - Container dikunci tingginya (100vh - 70px).
    - Overflow hidden di body utama.
    - Scroll hanya terjadi di dalam Card Body.
--}}
<div class="container d-flex flex-column" style="height: calc(100vh - 70px); overflow: hidden;">
    
    {{-- BAGIAN ATAS (Header & Toolbar) - Diam/Sticky --}}
    <div class="flex-shrink-0 pt-4 mb-3">
        {{ $header }}
    </div>

    {{-- BAGIAN TENGAH (Tabel) - Mengisi sisa ruang & Scrollable Internal --}}
    <div class="card shadow-sm border-0 mb-3 flex-grow-1 d-flex flex-column" style="overflow: hidden;">
        <div class="card-body p-0 d-flex flex-column" style="overflow-y: auto;">
            {{ $slot }}
        </div>
        
        {{-- BAGIAN BAWAH (Pagination) - Diam/Sticky --}}
        @if (isset($footer))
            <div class="card-footer bg-white py-3 flex-shrink-0">
                {{ $footer }}
            </div>
        @endif
    </div>
</div>