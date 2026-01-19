@extends('layouts.app')

@section('title', 'Detail Proposal')

@section('content')
    @php
        // 1. CEK PERAN USER
        $currentUserId = Auth::id();
        $isPengusul = $currentUserId == $detailProposal->user_id;
        
        // 2. CEK STATUS UNDANGAN (Khusus Dosen Anggota)
        // Variable $statusUndanganSaya & $idUndanganSaya dikirim dari Controller Dosen
        $isInvitedMember = isset($statusUndanganSaya) && $statusUndanganSaya === 0 && isset($idUndanganSaya);

        // 3. CEK APAKAH USER BOLEH VALIDASI (Wadek/Dekan/Kapus)
        // Variable $canValidate dikirim true dari Controller Role Penilai
        $enableValidation = $canValidate ?? false; 
    @endphp

    {{-- LIBRARY CONFETTI (Hanya jika Dosen Anggota) --}}
    @if($isInvitedMember)
        <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
    @endif

    {{-- ALERT CONTAINER --}}
    @include('shared.alert_script')

    <div class="container pb-5">

        {{-- HEADER: TOMBOL KEMBALI DINAMIS --}}
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h4 class="fw-bold mb-1">Detail Proposal Usulan</h4>
                <p class="text-muted mb-0">Tinjau kembali data proposal secara lengkap.</p>
            </div>
            {{-- $backRoute dikirim dari Controller masing-masing role --}}
            <a href="{{ $backRoute ?? url()->previous() }}" class="btn btn-secondary d-flex align-items-center">
                <i class="bi bi-arrow-left me-2"></i> Kembali
            </a>
        </div>

        <div class="row">
            {{-- ================================================= --}}
            {{-- KOLOM KIRI: DATA STATIS (SAMA SEMUA ROLE) --}}
            {{-- ================================================= --}}
            <div class="col-lg-8">
                
                {{-- 1. IDENTITAS --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-info-circle me-2"></i>Identitas Usulan</h6>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Judul Kegiatan</label>
                                <p class="fs-5 fw-bold text-dark mb-0">{{ $detailProposal->judul }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold d-block mb-1">Status Saat Ini</label>
                                {{-- Gunakan Accessor Model jika ada, atau fallback --}}
                                <span class="badge bg-{{ $detailProposal->status_class ?? 'primary' }} fs-6 px-3 py-2 rounded-pill">
                                    {{ $detailProposal->status_text ?? $detailProposal->status }}
                                </span>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Skema Pengabdian</label>
                                {{-- Logic nama skema sebaiknya ada di Model (Accessor), tapi jika di view: --}}
                                <p class="mb-0">{{ $detailProposal->nama_skema_lengkap ?? $detailProposal->skema }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="small text-muted fw-bold">Tahun Pelaksanaan</label>
                                <p class="mb-0">{{ $detailProposal->tahun_pelaksanaan }} ({{ $detailProposal->periode_kegiatan ?? 1 }} Tahun)</p>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="small text-muted fw-bold">Bidang Fokus</label>
                            <p class="mb-0 text-dark">{{ $detailProposal->bidang_fokus ?? '-' }}</p>
                        </div>
                    </div>
                </div>

                {{-- 2. SUBSTANSI --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-file-text me-2"></i>Substansi Kegiatan</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-4">
                            <label class="small text-muted fw-bold mb-2">Abstrak / Ringkasan</label>
                            <div class="p-3 bg-light rounded text-secondary" style="font-style: italic;">
                                "{{ $detailProposal->abstrak }}"
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">Mitra Sasaran</label>
                                <p class="mb-0 fw-bold">{{ $detailProposal->mitra }}</p>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="small text-muted fw-bold">Lokasi Kegiatan</label>
                                <p class="mb-0">{{ $detailProposal->lokasi }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. RAB --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-cash-stack me-2"></i>Rencana Anggaran Biaya</h6>
                        <span class="badge bg-success fs-6">Total: Rp {{ number_format($detailProposal->total_biaya, 0, ',', '.') }}</span>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <thead class="table-light">
                                <tr><th class="ps-4">Item Pengeluaran</th><th class="text-end pe-4">Biaya (Rp)</th></tr>
                            </thead>
                            <tbody>
                                <tr><td class="ps-4">Honor Output</td><td class="text-end pe-4">{{ number_format($detailProposal->honor_output, 0, ',', '.') }}</td></tr>
                                <tr><td class="ps-4">Belanja Non Operasional</td><td class="text-end pe-4">{{ number_format($detailProposal->belanja_non_operasional, 0, ',', '.') }}</td></tr>
                                <tr><td class="ps-4">Bahan Habis Pakai</td><td class="text-end pe-4">{{ number_format($detailProposal->bahan_habis_pakai, 0, ',', '.') }}</td></tr>
                                <tr><td class="ps-4">Transportasi</td><td class="text-end pe-4">{{ number_format($detailProposal->transportasi, 0, ',', '.') }}</td></tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ================================================= --}}
            {{-- KOLOM KANAN: ACTION & LAMPIRAN --}}
            {{-- ================================================= --}}
            <div class="col-lg-4">
                
                {{-- A. PANEL AKSI (DINAMIS SESUAI ROLE) --}}
                
                {{-- CASE 1: DOSEN ANGGOTA (TERIMA/TOLAK UNDANGAN) --}}
                @if($isInvitedMember)
                    <div class="card shadow border-0 mb-4 bg-warning bg-opacity-10 border-warning">
                        <div class="card-body">
                            <h6 class="fw-bold text-dark mb-3"><i class="bi bi-shield-check me-2"></i>Keputusan Bergabung</h6>
                            <p class="small text-muted mb-3">Anda diundang menjadi anggota. Silakan konfirmasi:</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-success fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTerimaAnggota">
                                    <i class="bi bi-check-circle-fill me-2"></i> Terima
                                </button>
                                <button class="btn btn-danger fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTolakAnggota">
                                    <i class="bi bi-x-circle-fill me-2"></i> Tolak
                                </button>
                            </div>
                        </div>
                    </div>
                
                {{-- CASE 2: VALIDATOR (WADEK/DEKAN/KAPUS) --}}
                @elseif($enableValidation)
                    <div class="card shadow border-0 mb-4 bg-primary bg-opacity-10 border-primary">
                        <div class="card-body">
                            <h6 class="fw-bold text-primary mb-3"><i class="bi bi-gavel me-2"></i>Validasi Proposal</h6>
                            <p class="small text-muted mb-3">Tentukan status proposal ini:</p>
                            <div class="d-grid gap-2">
                                <button class="btn btn-success fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalSetujuProposal">
                                    <i class="bi bi-check-lg me-2"></i> Setujui Proposal
                                </button>
                                <button class="btn btn-danger fw-bold text-white shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTolakProposal">
                                    <i class="bi bi-x-lg me-2"></i> Tolak Proposal
                                </button>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- B. TIM PELAKSANA --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-people me-2"></i>Tim Pelaksana</h6>
                    </div>
                    <div class="list-group list-group-flush">
                        @foreach ($anggota as $item)
                            <div class="list-group-item px-3 py-3">
                                <div class="d-flex align-items-center">
                                    <div class="flex-shrink-0 bg-light rounded-circle d-flex align-items-center justify-content-center text-primary fw-bold" style="width: 40px; height: 40px;">
                                        {{ str_contains($item->peran, 'Mahasiswa') ? 'M' : 'D' }}
                                    </div>
                                    <div class="ms-3 flex-grow-1">
                                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.9rem;">{{ $item->nama }}</h6>
                                        <small class="text-muted d-block">{{ $item->peran }}</small>
                                        {{-- Badge Status Anggota --}}
                                        @if(!str_contains($item->peran, 'Mahasiswa'))
                                            @if($item->is_approved_dosen == 1 || str_contains($item->peran, 'Ketua'))
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill mt-1" style="font-size: 0.65rem;">Confirmed</span>
                                            @elseif($item->is_approved_dosen == 2)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill mt-1" style="font-size: 0.65rem;">Menolak</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill mt-1" style="font-size: 0.65rem;">Menunggu</span>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- C. FILE PROPOSAL UTAMA --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-body">
                        <div class="d-grid">
                            <a href="{{ \Storage::url($detailProposal->file_proposal) }}" target="_blank" class="btn btn-outline-primary fw-bold py-2 border-2">
                                <i class="bi bi-file-earmark-pdf-fill me-2"></i> Lihat File Proposal
                            </a>
                        </div>
                    </div>
                </div>

                {{-- D. DOKUMEN & LUARAN (Tombol Upload hanya untuk Pengusul) --}}
                {{-- ... (Gunakan include jika ingin lebih rapi, tapi disini saya tulis langsung) ... --}}
                
                {{-- DOKUMEN --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-paperclip me-2"></i>Lampiran</h6>
                        @if($isPengusul)<button class="btn btn-sm btn-outline-primary fw-bold" data-bs-toggle="modal" data-bs-target="#modaldokumen"><i class="bi bi-plus-lg"></i></button>@endif
                    </div>
                    <div class="card-body">
                        @forelse($lampiran->where('kategori', 'dokumen') as $item)
                            <a href="{{ \Storage::url($item->file_path) }}" target="_blank" class="btn btn-outline-secondary btn-sm w-100 mb-2 text-start"><i class="bi bi-file-earmark me-2"></i> {{ $item->judul }}</a>
                        @empty
                            <p class="text-muted small text-center mb-0">Tidak ada lampiran.</p>
                        @endforelse
                    </div>
                </div>

                {{-- LUARAN (Artikel, HKI, dll) --}}
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-primary mb-0"><i class="bi bi-archive me-2"></i>Luaran Kegiatan</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            {{-- ARTIKEL --}}
                            <button class="btn btn-outline-success border text-start d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalListArtikel">
                                <span><i class="bi bi-journal-text me-2"></i> Artikel</span>
                                <span class="badge bg-success rounded-pill">{{ $lampiran->where('kategori', 'artikel')->count() }}</span>
                            </button>
                            {{-- SERTIFIKAT --}}
                            <button class="btn btn-outline-success border text-start d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalListBuku">
                                <span><i class="bi bi-award me-2"></i> Sertifikat</span>
                                <span class="badge bg-success rounded-pill">{{ $lampiran->where('kategori', 'sertifikat')->count() }}</span>
                            </button>
                            {{-- HKI --}}
                            <button class="btn btn-outline-success border text-start d-flex justify-content-between align-items-center" data-bs-toggle="modal" data-bs-target="#modalListHKI">
                                <span><i class="bi bi-patch-check me-2"></i> HKI</span>
                                <span class="badge bg-success rounded-pill">{{ $lampiran->where('kategori', 'hki')->count() }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- DOWNLOAD PENGESAHAN --}}
                <div class="d-flex justify-content-end mt-1 mb-5">
                    <a href="{{ route('dosen.proposal.export_pdf', $detailProposal->id) }}" class="btn btn-success btn-lg px-3 w-100 fw-bold shadow-sm">
                        <i class="bi bi-file-earmark-pdf-fill me-2"></i> Lembar Pengesahan
                    </a>
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- AREA MODAL (CONDITIONAL) --}}
    {{-- ========================================================================= --}}

    {{-- 1. MODAL DOSEN ANGGOTA --}}
    @if($isInvitedMember)
        {{-- Modal Terima --}}
        <div class="modal fade" id="modalTerimaAnggota" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white"><h5 class="modal-title fw-bold">Konfirmasi Bergabung</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <form action="{{ route('dosen.anggota.terima', $idUndanganSaya) }}" method="POST">
                        @csrf 
                        <div class="modal-body text-center p-4">
                            <h6 class="fw-bold">Anda bersedia bergabung?</h6>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success fw-bold" onclick="triggerConfetti()">Ya, Saya Bersedia</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        {{-- Modal Tolak --}}
        <div class="modal fade" id="modalTolakAnggota" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white"><h5 class="modal-title fw-bold">Tolak Undangan</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <form action="{{ route('dosen.anggota.tolak', $idUndanganSaya) }}" method="POST">
                        @csrf 
                        <div class="modal-body text-center p-4">
                            <h6 class="fw-bold">Tolak undangan ini?</h6>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger fw-bold">Ya, Tolak</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- 2. MODAL VALIDASI (WADEK/DEKAN) --}}
    @if($enableValidation)
        @php
            // Tentukan route validasi berdasarkan role context (dikirim dari controller)
            // Misal $validationRouteName = 'wadek3.validasi.keputusan' atau 'dekan.validasi.keputusan'
            $routeValidation = isset($validationRouteName) ? route($validationRouteName, $detailProposal->id) : '#';
        @endphp

        {{-- Modal Setuju Proposal --}}
        <div class="modal fade" id="modalSetujuProposal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-success text-white"><h5 class="modal-title fw-bold">Validasi: Setujui</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <form action="{{ $routeValidation }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="keputusan" value="terima">
                        <div class="modal-body text-center p-4">
                            <h6 class="fw-bold">Setujui proposal ini?</h6>
                            <p class="text-muted small">Proposal akan lanjut ke tahap berikutnya.</p>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-success fw-bold">Ya, Setujui</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        {{-- Modal Tolak Proposal --}}
        <div class="modal fade" id="modalTolakProposal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white"><h5 class="modal-title fw-bold">Validasi: Tolak</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div>
                    <form action="{{ $routeValidation }}" method="POST">
                        @csrf @method('PATCH')
                        <input type="hidden" name="keputusan" value="tolak">
                        <div class="modal-body p-4">
                            <div class="mb-3">
                                <label class="form-label fw-bold">Alasan Penolakan</label>
                                <textarea name="feedback" class="form-control" rows="3" required placeholder="Tuliskan alasan..."></textarea>
                            </div>
                        </div>
                        <div class="modal-footer justify-content-center">
                            <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" class="btn btn-danger fw-bold">Kirim Penolakan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    {{-- 3. MODAL UPLOAD (HANYA PENGUSUL) --}}
    @if($isPengusul)
        {{-- Include modal upload dokumen, artikel, sertifikat, hki disini (sama seperti kode dosen asli) --}}
        @include('shared.partials.modals_upload_lampiran') 
        {{-- CATATAN: Buat file partial 'modals_upload_lampiran.blade.php' dan pindahkan modal upload kesana agar file ini tidak terlalu panjang --}}
    @else
        {{-- Modal List View Only (Jika perlu melihat list detail tapi tidak bisa upload) --}}
        @include('shared.partials.modals_view_lampiran')
    @endif

@endsection

@push('scripts')
    <script>
        function triggerConfetti() {
            var end = Date.now() + (1 * 1000);
            var colors = ['#378a75', '#ffffff'];
            (function frame() {
                confetti({ particleCount: 3, angle: 60, spread: 55, origin: { x: 0 }, colors: colors });
                confetti({ particleCount: 3, angle: 120, spread: 55, origin: { x: 1 }, colors: colors });
                if (Date.now() < end) requestAnimationFrame(frame);
            }());
        }
    </script>
@endpush