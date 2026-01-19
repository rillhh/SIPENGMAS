<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class DosenListStatistikController extends Controller
{
    public function showList(Request $request, $kategori)
    {
        $user = Auth::user();
        $userId = $user->id;
        $userNidn = $user->nidn;

        // Setup Filter
        $perPage = in_array($request->input('per_page'), [5, 10, 25, 50]) ? $request->input('per_page') : 10;
        $search = $request->input('search');
        
        $currentYear = date('Y');
        $selectedYear = $request->input('year', $currentYear);
        $years = range($currentYear + 1, 2019); // Descending

        // ==========================================================
        // 1. QUERY BASE PROPOSAL (Untuk Pengabdian, Anggota, Semua)
        // ==========================================================
        if (in_array($kategori, ['pengabdian', 'anggota', 'semua_usulan'])) {
            
            // Set Judul Halaman
            $pageTitle = match ($kategori) {
                'pengabdian' => 'Riwayat Pengabdian Didanai',
                'anggota'    => 'Riwayat Sebagai Anggota',
                default      => 'Semua Riwayat Usulan',
            };

            // Eloquent Query dengan Relasi Eager Loading
            $query = Proposal::with(['identitas', 'user', 'biaya'])
                             ->where('tahun_pelaksanaan', $selectedYear);

            // --- FILTER PER KATEGORI ---
            if ($kategori == 'pengabdian') {
                // Hanya Proposal yang DIDANAI (Status Selesai)
                // Prodi: Status 3, Pusat: Status 4. Atau Status 4 (General Success)
                // Kita gunakan logika umum: Status >= 3 (Sudah lewat validasi pusat/wadek)
                // Atau lebih ketat: Status == 4 (Didanai Final)
                $query->where('status_progress', 4); // Ubah sesuai definisi 'Didanai' Anda

                // User harus terlibat (Ketua ATAU Anggota Approved)
                $query->where(function($q) use ($userId, $userNidn) {
                    $q->where('user_id', $userId)
                      ->orWhereHas('anggotaDosen', function($sub) use ($userNidn) {
                          $sub->where('nidn', $userNidn)
                              ->where('is_approved_dosen', 1);
                      });
                });

            } elseif ($kategori == 'anggota') {
                // Hanya Proposal Orang Lain (User != Ketua)
                $query->where('user_id', '!=', $userId)
                      ->whereHas('anggotaDosen', function($sub) use ($userNidn) {
                          $sub->where('nidn', $userNidn)
                              ->where('is_approved_dosen', 1);
                      });

            } elseif ($kategori == 'semua_usulan') {
                // Semua Proposal (Ketua ATAU Anggota Approved)
                $query->where(function($q) use ($userId, $userNidn) {
                    $q->where('user_id', $userId)
                      ->orWhereHas('anggotaDosen', function($sub) use ($userNidn) {
                          $sub->where('nidn', $userNidn)
                              ->where('is_approved_dosen', 1);
                      });
                });
            }

            // Search
            if ($search) {
                $query->whereHas('identitas', function($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%");
                });
            }

            // Sorting & Pagination
            $items = $query->orderBy('created_at', 'desc')
                           ->paginate($perPage)
                           ->withQueryString();

            // Transformasi manual TIDAK DIPERLUKAN LAGI
            // Karena View akan memanggil Accessor: $item->status_label, $item->total_dana, dll.
        }

        // ==========================================================
        // 2. QUERY BASE LUARAN (Artikel, HKI, Sertifikat)
        // ==========================================================
        else {
            $titles = [
                'artikel'    => 'Daftar Artikel Ilmiah',
                'sertifikat' => 'Daftar Sertifikat',
                'hki'        => 'Daftar HKI',
                'dokumen'    => 'Daftar Dokumen Lampiran'
            ];
            $pageTitle = $titles[$kategori] ?? ucfirst($kategori);

            // Eloquent Query ke Model ProposalLampiran
            $query = ProposalLampiran::with(['proposal.identitas', 'proposal.user'])
                ->where('kategori', $kategori);

            // Filter Tahun via Relasi Proposal
            $query->whereHas('proposal', function($q) use ($selectedYear) {
                $q->where('tahun_pelaksanaan', $selectedYear);
            });

            // Filter Hak Akses (Milik Sendiri atau Tim) via Relasi Proposal
            $query->whereHas('proposal', function($q) use ($userId, $userNidn) {
                $q->where('user_id', $userId)
                  ->orWhereHas('anggotaDosen', function($sub) use ($userNidn) {
                      $sub->where('nidn', $userNidn)
                          ->where('is_approved_dosen', 1);
                  });
            });

            // Search
            if ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('judul', 'like', "%{$search}%")
                      ->orWhereHas('proposal.identitas', function($sub) use ($search) {
                          $sub->where('judul', 'like', "%{$search}%");
                      });
                });
            }

            // Sorting & Pagination
            $items = $query->orderBy('created_at', 'desc')
                           ->paginate($perPage)
                           ->withQueryString();
        }

        return view('dosen.dosen_list_statistik', compact(
            'items', 'pageTitle', 'kategori', 'search', 'years', 'selectedYear', 'perPage' 
        ));
    }
}