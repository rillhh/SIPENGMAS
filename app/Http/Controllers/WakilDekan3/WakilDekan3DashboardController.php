<?php

namespace App\Http\Controllers\WakilDekan3;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class WakilDekan3DashboardController extends Controller
{
    public function index()
    {
        // 1. DATA NOTIFIKASI & LIST PENDING
        // Ambil proposal yang MENUNGGU validasi Wadek 3 (Status = 1)
        // Accessor 'skema_label' otomatis tersedia di model, tidak perlu transform manual.
        $pendingProposals = Proposal::with(['identitas', 'user', 'skemaRef'])
            ->where('status_progress', 1) 
            ->orderBy('updated_at', 'asc') // FIFO
            ->get();

        // 2. STATISTIK UTAMA
        $stats = [
            'total_usulan'     => Proposal::where('status_progress', '>=', 1)->count(),
            'disetujui'        => Proposal::where('status_progress', '>=', 2)->count(),
            'ditolak'          => Proposal::where('status_progress', 99)->count(),
            'pending_count'    => $pendingProposals->count(),
            
            // Statistik Luaran
            'total_artikel'    => $this->countLuaran('artikel'),
            'total_sertifikat' => $this->countLuaran('sertifikat'),
            'total_hki'        => $this->countLuaran('hki'),
        ];

        return view('wakil_dekan3.wakil_dekan3_dashboard', compact('pendingProposals', 'stats'));
    }

    private function countLuaran($kategori)
    {
        return ProposalLampiran::where('kategori', $kategori)
            ->whereHas('proposal', function($q) {
                $q->where('status_progress', '>=', 2);
            })
            ->count();
    }
}