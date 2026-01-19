<?php

namespace App\Http\Controllers\Dekan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class DekanListStatistikController extends Controller
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
        if (in_array($kategori, ['total_usulan', 'disetujui', 'ditolak', 'menunggu'])) {
            
            // Judul Halaman Dinamis
            $pageTitle = match($kategori) {
                'total_usulan' => 'Semua Riwayat Usulan Prodi',
                'disetujui'    => 'Proposal Prodi Disetujui',
                'ditolak'      => 'Proposal Prodi Ditolak',
                'menunggu'     => 'Proposal Menunggu Validasi',
                default        => 'Daftar Proposal'
            };

            // Gunakan Eloquent dengan Relasi
            $query = Proposal::with(['identitas', 'user', 'biaya'])
                             ->where('skala_pelaksanaan', 'Prodi') // FILTER KHUSUS DEKAN
                             ->where('tahun_pelaksanaan', $selectedYear);

            // LOGIKA FILTER STATUS
            if ($kategori == 'total_usulan') {
                $query->where('status_progress', '>=', 1);
            } elseif ($kategori == 'disetujui') {
                $query->where('status_progress', '>=', 2);
            } elseif ($kategori == 'ditolak') {
                $query->where('status_progress', 99);
            } elseif ($kategori == 'menunggu') {
                $query->where('status_progress', 1);
            }

            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->whereHas('identitas', fn($sub) => $sub->where('judul', 'like', "%{$search}%"))
                      ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
                });
            }
            
            // Get Data
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
                    $q->where('skala_pelaksanaan', 'Prodi') // FILTER KHUSUS DEKAN
                      ->where('tahun_pelaksanaan', $selectedYear)
                      ->where('status_progress', '>=', 2); // Hanya luaran dari proposal valid
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

        return view('dekan.dekan_list_statistik', compact(
            'items', 'pageTitle', 'kategori', 'search', 'years', 'selectedYear', 'perPage'
        ));
    }
}