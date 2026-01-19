{{-- KOLOM TENGAH: PENGABDIAN --}}
<div class="col-lg-5 col-md-6">
    <div class="d-flex flex-column gap-2 h-100">
        {{-- CARD 1: TOTAL USULAN --}}
        <a href="{{ route('wakil_rektor.statistik.list', 'total_usulan') }}" class="text-decoration-none flex-grow-1">
            <div class="card shadow-sm h-100 stat-card">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['total_usulan'] }}</h4>
                        <small class="text-muted" style="font-size: 1rem;">Total Usulan Masuk</small>
                    </div>
                    {{-- Icon People Group / Data --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#8BC3B4" viewBox="0 0 16 16">
                        <path d="M15 14s1 0 1-1-1-4-5-4-5 3-5 4 1 1 1 1zm-7.978-1L7 12.996c.001-.264.167-1.03.76-1.72C8.312 10.629 9.282 10 11 10c1.717 0 2.687.63 3.24 1.276.593.69.758 1.457.76 1.72l-.008.002H15a1 1 0 0 0 1-1 1 1 0 0 0-1-1H3a1 1 0 0 0-1 1 1 1 0 0 0 1 1zM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4m.256 7a4.474 4.474 0 0 1-.229-1.004H3c.001-.246.154-.986.832-1.664C4.484 10.68 5.711 10 8 10c.26 0 .51.028.745.085zM11 5a3 3 0 1 1-6 0 3 3 0 0 1 6 0M8 7a2 2 0 1 0 0-4 2 2 0 0 0 0 4"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- CARD 2: PROPOSAL DISETUJUI --}}
        <a href="{{ route('wakil_rektor.statistik.list', 'proposal_disetujui') }}" class="text-decoration-none flex-grow-1">
            <div class="card shadow-sm h-100 stat-card">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h4 class="fw-bold mb-0 text-dark">{{ $stats['disetujui'] }}</h4>
                        <small class="text-muted" style="font-size: 1rem;">Proposal Disetujui</small>
                    </div>
                    {{-- Icon Person Check --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="36" height="36" fill="#8BC3B4" class="bi bi-person-check-fill" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M15.854 5.146a.5.5 0 0 1 0 .708l-3 3a.5.5 0 0 1-.708 0l-1.5-1.5a.5.5 0 0 1 .708-.708L12.5 7.793l2.646-2.647a.5.5 0 0 1 .708 0z"/>
                        <path d="M1 14s-1 0-1-1 1-4 6-4 6 3 6 4-1 1-1 1H1zm5-6a3 3 0 1 0 0-6 3 3 0 0 0 0 6z"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>
</div>

{{-- KOLOM KANAN: LUARAN --}}
<div class="col-lg-4 col-md-6">
    <div class="d-flex flex-column gap-2 h-100">
        {{-- CARD 3: ARTIKEL --}}
        <a href="{{ route('wakil_rektor.statistik.list', 'artikel') }}" class="text-decoration-none flex-grow-1">
            <div class="card shadow-sm h-100 stat-card">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $stats['total_artikel'] }}</h5>
                        <small class="text-muted" style="font-size: 1rem;">Total Artikel</small>
                    </div>
                    {{-- Icon File Text --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#8BC3B4" class="bi bi-file-earmark-text-fill" viewBox="0 0 16 16">
                        <path d="M5 4a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm-.5 2.5A.5.5 0 0 1 5 6h6a.5.5 0 0 1 0 1H5a.5.5 0 0 1-.5-.5M5 8a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/>
                        <path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- CARD 4: SERTIFIKAT --}}
        <a href="{{ route('wakil_rektor.statistik.list', 'sertifikat') }}" class="text-decoration-none flex-grow-1">
            <div class="card shadow-sm h-100 stat-card">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $stats['total_sertifikat'] }}</h5>
                        <small class="text-muted" style="font-size: 1rem;">Total Sertifikat</small>
                    </div>
                    {{-- Icon Book / Journal --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#8BC3B4" class="bi bi-journal-bookmark-fill" viewBox="0 0 16 16">
                        <path d="M1 2.828c.885-.37 2.154-.769 3.388-.893 1.33-.134 2.458.063 3.112.752v9.746c-.935-.53-2.12-.603-3.213-.493-1.18.12-2.37.461-3.287.893V2.828zM5 1.802c.556-.07 1.255-.11 1.954-.057 1.312.1 2.308.455 2.946.892V13.5c-.86-.445-1.956-.566-3.046-.465-1.03.095-2.06.393-2.846.776V1.802zM14.5 3.13c-1.135-.42-2.372-.72-3.486-.803-1.229-.092-2.31.06-2.94.54V13.5c.85-.41 1.92-.515 2.946-.464 1.05.05 2.09.32 2.94.67V3.13zM15 2.828v9.746c.935-.53 2.12-.603 3.213-.493 1.18.12 2.37.461 3.287.893V1.802c-.556-.07-1.255-.11-1.954-.057-1.312.1-2.308.455-2.946.892z"/>
                    </svg>
                </div>
            </div>
        </a>

        {{-- CARD 5: HKI --}}
        <a href="{{ route('wakil_rektor.statistik.list', 'hki') }}" class="text-decoration-none flex-grow-1">
            <div class="card shadow-sm h-100 stat-card">
                <div class="card-body p-3 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">{{ $stats['total_hki'] }}</h5>
                        <small class="text-muted" style="font-size: 1rem;">Total HKI</small>
                    </div>
                    {{-- Icon Award --}}
                    <svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#8BC3B4" class="bi bi-award-fill" viewBox="0 0 16 16">
                        <path d="m8 0 1.669.864 1.858.282.842 1.68 1.337 1.32L13.4 6l.306 1.854-1.337 1.32-.842 1.68-1.858.282L8 12l-1.669-.864-1.858-.282-.842-1.68-1.337-1.32L2.6 6l-.306-1.854 1.337-1.32.842-1.68 1.858-.282L8 0z"/>
                        <path d="M4 11.794V16l4-1 4 1v-4.206l-2.018.306L8 13.126 6.018 12.1 4 11.794z"/>
                    </svg>
                </div>
            </div>
        </a>
    </div>
</div>

<style>
    .stat-card:hover { border-color: #378a75 !important; transition: border-color 0.3s ease; }
</style>