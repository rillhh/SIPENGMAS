<?php

namespace App\Http\Controllers\KepalaPusat;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Proposal;
use App\Models\ProposalLampiran;
use Carbon\Carbon;
use App\Models\Skema;

class KepalaPusatDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // 1. TENTUKAN ID SKEMA BERDASARKAN ROLE
        $targetSkemaId = 0;
        $skemas = Skema::all();

        foreach ($skemas as $skema) {
            // stripos = Case Insensitive check
            // Cek apakah "Pusat Yarsi Peduli TB" ada di dalam string "Kepala Pusat Yarsi Peduli TB"
            if ($skema->label_dropdown && stripos($user->name, $skema->label_dropdown) !== false) {
                $targetSkemaId = $skema->id;
                break; // Ketemu, stop looping
            }
        }

        // Fallback: Jika pencocokan nama gagal, coba ambil angka dari role (cara lama)
        if ($targetSkemaId == 0) {
            $targetSkemaId = (int) filter_var($user->role, FILTER_SANITIZE_NUMBER_INT);
        }

        // 2. BASE QUERY
        $baseQuery = Proposal::where('skala_pelaksanaan', 'Pusat')
                             ->where('skema', $targetSkemaId)
                             ->where('status_progress', '>=', 2);
                             

        // 3. HITUNG STATISTIK
        $stats = [
            'total_usulan' => (clone $baseQuery)->count(),
            'disetujui'    => (clone $baseQuery)->where('status_progress', '>', 2)->count(),
            'pending_count' => (clone $baseQuery)->where('status_progress', 2)->count(),
            'total_artikel'    => $this->countLuaran('artikel', $targetSkemaId),
            'total_sertifikat' => $this->countLuaran('sertifikat', $targetSkemaId),
            'total_hki'        => $this->countLuaran('hki', $targetSkemaId),
        ];

        // 4. DAFTAR PROPOSAL PENDING
        // Load relasi 'identitas' dan 'user' untuk ditampilkan di Dashboard
        $pendingProposals = Proposal::with(['identitas', 'user'])
            ->where('skala_pelaksanaan', 'Pusat')
            ->where('skema', $targetSkemaId)
            ->where('status_progress', 2) 
            ->orderBy('updated_at', 'asc') // FIFO
            ->get();

        return view('kepala_pusat.kepala_pusat_dashboard', compact('stats', 'pendingProposals'));
    }

    private function countLuaran($kategori, $skemaId)
    {
        return ProposalLampiran::where('kategori', $kategori)
            ->whereHas('proposal', function($q) use ($skemaId) {
                $q->where('skala_pelaksanaan', 'Pusat')
                  ->where('skema', $skemaId)
                  ->where('status_progress', '>', 2);
            })
            ->count();
    }
}