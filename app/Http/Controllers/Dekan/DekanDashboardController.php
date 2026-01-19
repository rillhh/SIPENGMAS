<?php

namespace App\Http\Controllers\Dekan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal; 
use App\Models\ProposalLampiran; 
use Illuminate\Support\Facades\Auth;

class DekanDashboardController extends Controller
{
    public function index()
    {
        // 1. DATA MONITORING TERBARU (Limit 5)
        // Ambil proposal skala Prodi (sesuai lingkup Dekan)
        $pendingProposals = Proposal::with(['identitas', 'user'])
            ->where('skala_pelaksanaan', 'Prodi')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        // 2. STATISTIK UTAMA
        $baseQuery = Proposal::where('skala_pelaksanaan', 'Prodi');

        $stats = [
            'total_usulan'  => (clone $baseQuery)->count(),
            'disetujui'     => (clone $baseQuery)->where('status_progress', '>=', 2)->count(),
            'menunggu'      => (clone $baseQuery)->where('status_progress', 1)->count(), // Di Wadek
            'ditolak'       => (clone $baseQuery)->where('status_progress', 99)->count(),
            
            // Statistik Luaran
            'total_artikel'    => $this->countLuaran('artikel'),
            'total_sertifikat' => $this->countLuaran('sertifikat'),
            'total_hki'        => $this->countLuaran('hki'),
        ];

        return view('dekan.dekan_dashboard', compact('stats', 'pendingProposals'));
    }

    private function countLuaran($kategori)
    {
        // Hitung luaran hanya dari proposal skala Prodi yang sudah jalan/selesai
        return ProposalLampiran::where('kategori', $kategori)
            ->whereHas('proposal', function($q) {
                $q->where('skala_pelaksanaan', 'Prodi')
                  ->where('status_progress', '>=', 2);
            })
            ->count();
    }
}