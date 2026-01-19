<?php

namespace App\Http\Controllers\WakilRektor3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class WakilRektor3ListStatistikController extends Controller
{
    public function showList(Request $request, $kategori)
    {
        // 1. SETUP FILTER
        $perPage = in_array($request->input('per_page'), [5, 10, 25, 50]) ? $request->input('per_page') : 10;
        $search = $request->input('search');
        
        $currentYear = date('Y');
        $selectedYear = $request->input('year', $currentYear);
        $years = range($currentYear + 1, 2019);

        // ==========================================================
        // A. QUERY PROPOSAL (Total Usulan & Disetujui)
        // ==========================================================
        if (in_array($kategori, ['total_usulan', 'proposal_disetujui'])) {
            
            $pageTitle = ($kategori == 'total_usulan') ? 'Total Usulan Universitas' : 'Proposal Disetujui (Didanai)';

            // Gunakan Eloquent dengan Relasi
            $query = Proposal::with(['identitas', 'user', 'biaya'])
                             ->where('tahun_pelaksanaan', $selectedYear);

            // LOGIKA FILTER STATUS (Flow Warek 3)
            if ($kategori == 'total_usulan') {
                // Semua yang masuk sistem (Status >= 1)
                $query->where('status_progress', '>=', 1);
            } else {
                // Hanya yang sudah Final/Didanai (Status 4)
                $query->where('status_progress', 4);
            }

            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('identitas', fn($sub) => $sub->where('judul', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
                });
            }
            
            // Get Data (Tidak perlu transform manual lagi)
            $items = $query->latest('updated_at')
                           ->paginate($perPage)
                           ->withQueryString();

        } 
        // ==========================================================
        // B. QUERY LUARAN (Artikel, Sertifikat, HKI)
        // ==========================================================
        else {
            $titles = ['artikel' => 'Rekap Artikel', 'sertifikat' => 'Rekap Sertifikat', 'hki' => 'Rekap HKI'];
            $pageTitle = $titles[$kategori] ?? ucfirst($kategori);

            // Gunakan Eloquent ProposalLampiran
            $query = ProposalLampiran::with(['proposal.identitas', 'proposal.user'])
                ->where('kategori', $kategori)
                ->whereHas('proposal', function($q) use ($selectedYear) {
                    $q->where('tahun_pelaksanaan', $selectedYear)
                      ->where('status_progress', 4); // Hanya luaran dari proposal didanai
                });

            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhereHas('proposal.identitas', fn($sub) => $sub->where('judul', 'like', "%{$search}%"));
                });
            }

            $items = $query->latest()->paginate($perPage)->withQueryString();
        }

        return view('wakil_rektor3.wakil_rektor3_list_statistik', compact(
            'items', 'pageTitle', 'kategori', 'search', 'years', 'selectedYear', 'perPage'
        ));
    }
}