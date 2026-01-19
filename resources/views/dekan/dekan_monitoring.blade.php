@extends('layouts.app')

@section('title', 'Monitoring Proposal')

@section('content')
    <div class="container">
        {{-- SHARED ALERT --}}
        @include('shared.alert_script')

        {{-- TOOLBAR --}}
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div>
                <a href="{{ route('dekan.dashboard') }}" class="btn btn-secondary btn-action-control shadow-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
            
            {{-- SHARED TOOLBAR (SEARCH + YEAR) --}}
            <div class="d-flex gap-2">
                @include('shared.toolbar_filter')
            </div>
        </div>

        {{-- HEADER --}}
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0 fw-bold text-primary">Monitoring Proposal</h1>
                <p class="text-muted">Pemantauan status proposal pengabdian di Fakultas.</p>
            </div>
        </div>

        {{-- TABS --}}
        <ul class="nav nav-tabs" id="validasiTab" role="tablist">
            <li class="nav-item">
                <button class="nav-link active" id="menunggu-tab" data-bs-toggle="tab" data-bs-target="#menunggu-pane" type="button" onclick="setActiveTab('menunggu')">
                    Menunggu Validasi <span class="badge bg-warning text-dark ms-1">{{ $data['menunggu']->total() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="disetujui-tab" data-bs-toggle="tab" data-bs-target="#disetujui-pane" type="button" onclick="setActiveTab('disetujui')">
                    Disetujui <span class="badge bg-success ms-1">{{ $data['disetujui']->total() }}</span>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" id="ditolak-tab" data-bs-toggle="tab" data-bs-target="#ditolak-pane" type="button" onclick="setActiveTab('ditolak')">
                    Ditolak <span class="badge bg-danger ms-1">{{ $data['ditolak']->total() }}</span>
                </button>
            </li>
        </ul>

        {{-- CONTENT TABS --}}
        <div class="card shadow-sm border-0 mb-5" style="border-top-left-radius: 0; border-top-right-radius: 0;">
            <div class="card-body p-0">
                <div class="tab-content" id="validasiTabContent">
                    
                    {{-- TAB 1: MENUNGGU --}}
                    <div class="tab-pane fade show active" id="menunggu-pane">
                        {{-- PERBAIKAN: Tambahkan 'type' => 'menunggu' --}}
                        @include('dekan.partials.table_monitoring', ['items' => $data['menunggu'], 'type' => 'menunggu'])
                    </div>
                    
                    {{-- TAB 2: DISETUJUI --}}
                    <div class="tab-pane fade" id="disetujui-pane">
                        {{-- PERBAIKAN: Tambahkan 'type' => 'disetujui' --}}
                        @include('dekan.partials.table_monitoring', ['items' => $data['disetujui'], 'type' => 'disetujui'])
                    </div>
                    
                    {{-- TAB 3: DITOLAK --}}
                    <div class="tab-pane fade" id="ditolak-pane">
                        {{-- PERBAIKAN: Tambahkan 'type' => 'ditolak' --}}
                        @include('dekan.partials.table_monitoring', ['items' => $data['ditolak'], 'type' => 'ditolak'])
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function changeYear(year) {
        const url = new URL(window.location.href);
        url.searchParams.set('year', year);
        window.location.href = url.toString();
    }
    function setActiveTab(tabName) {
        const url = new URL(window.location);
        url.searchParams.set('tab', tabName);
        window.history.pushState({}, '', url);
    }
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        const activeTab = urlParams.get('tab') || 'menunggu';
        const triggerEl = document.querySelector(`#${activeTab}-tab`);
        if (triggerEl) { new bootstrap.Tab(triggerEl).show(); }
    });
</script>
@endpush

@push('styles')
    <style>
        #validasiTab .nav-item .nav-link { color: #555 !important; background-color: #f8f9fa; border-bottom: none; margin-right: 2px; }
        #validasiTab .nav-item .nav-link.active { color: #378a75 !important; background-color: #fff; border-color: #dee2e6 #dee2e6 #fff; font-weight: 600; }
    </style>
@endpush