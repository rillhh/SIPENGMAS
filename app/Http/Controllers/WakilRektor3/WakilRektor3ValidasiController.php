<?php

namespace App\Http\Controllers\WakilRektor3;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\DB;
// [PENTING] Import Service Notifikasi
use App\Services\NotificationFlowService; 

class WakilRektor3ValidasiController extends Controller
{
    public function index(Request $request)
    {
        // 1. SETUP FILTER
        $currentYear = date('Y');
        $years = range($currentYear + 1, 2019);
        $selectedYear = $request->input('year', $currentYear);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        // 2. QUERY BASE (Relasi Lengkap)
        $baseQuery = Proposal::with(['user', 'identitas', 'biaya'])
            ->where('tahun_pelaksanaan', $selectedYear);

        // Search Logic
        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->whereHas('identitas', fn($sub) => $sub->where('judul', 'like', "%$search%"))
                  ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%$search%"));
            });
        }

        // 3. DATA PER TAB
        $data = [
            // Menunggu: Status 3 (Validasi Warek)
            'menunggu' => (clone $baseQuery)->where('status_progress', 3)
                ->orderBy('updated_at', 'asc') // FIFO
                ->paginate($perPage, ['*'], 'page_m')->withQueryString(),

            // Disetujui: Status 4 (Final / Didanai)
            'disetujui' => (clone $baseQuery)->where('status_progress', 4)
                ->orderByDesc('updated_at')
                ->paginate($perPage, ['*'], 'page_d')->withQueryString(),

            // Ditolak: Status 99
            'ditolak' => (clone $baseQuery)->where('status_progress', 99)
                ->orderByDesc('updated_at')
                ->paginate($perPage, ['*'], 'page_t')->withQueryString(),
        ];

        return view('wakil_rektor3.wakil_rektor3_validasi', compact('data', 'years', 'selectedYear', 'perPage'));
    }

    public function updateKeputusan(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);

        // VALIDASI STATUS (Harus 3)
        if ($proposal->status_progress != 3) {
            return back()->with('error', 'Gagal: Proposal tidak dalam antrian validasi Anda.');
        }

        $request->validate([
            'keputusan' => 'required|in:terima,tolak', 
            'feedback' => 'required_if:keputusan,tolak'
        ]);

        try {
            DB::transaction(function () use ($request, $proposal) {
                
                if ($request->keputusan == 'terima') {
                    // 1. UPDATE STATUS -> 4 (Final / Didanai)
                    $proposal->update(['status_progress' => 4]); 

                    // 2. KIRIM NOTIFIKASI SUKSES (Dosen, Anggota, Admin)
                    // [FIX] Memanggil Service Notifikasi
                    NotificationFlowService::warekApproved($proposal);

                } else {
                    // 1. UPDATE STATUS -> 99 (Ditolak)
                    $proposal->update([
                        'status_progress' => 99, 
                        'feedback' => $request->feedback
                    ]);

                    // 2. KIRIM NOTIFIKASI PENOLAKAN
                    // [FIX] Memanggil Service Notifikasi
                    NotificationFlowService::proposalRejected($proposal, $request->feedback, 'Wakil Rektor 3');
                }
            });

            $pesan = $request->keputusan == 'terima' ? 'Proposal berhasil disetujui dan didanai.' : 'Proposal telah ditolak.';
            return redirect()->route('wakil_rektor.validasi')->with('success', $pesan);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}