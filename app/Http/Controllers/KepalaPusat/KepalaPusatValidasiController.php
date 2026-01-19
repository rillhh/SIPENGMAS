<?php

namespace App\Http\Controllers\KepalaPusat;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Skema;

class KepalaPusatValidasiController extends Controller
{
    public function index(Request $request)
    {
        // 1. IDENTIFIKASI KEPALA PUSAT
        $user = Auth::user();
        
        // Ambil angka dari role (Misal: "Kepala Pusat 1" -> 1)
        $centerId = 0;
        $skemas = Skema::all();

        foreach ($skemas as $skema) {
            if ($skema->label_dropdown && stripos($user->name, $skema->label_dropdown) !== false) {
                $centerId = $skema->id;
                break;
            }
        }

        // Jika tidak ketemu, fallback ke Role ID
        if ($centerId == 0) {
            $centerId = (int) filter_var($user->role, FILTER_SANITIZE_NUMBER_INT);
        }

        if (!$centerId) {
            abort(403, 'Gagal mendeteksi Skema Pusat. Pastikan nama akun Kepala Pusat mengandung nama Pusat Studinya.');
        }

        // 2. SETUP FILTER
        $currentYear = date('Y');
        $years = range($currentYear + 1, 2019);
        $selectedYear = $request->input('year', $currentYear);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        // 3. QUERY BASE (ELOQUENT)
        $baseQuery = Proposal::with(['user', 'identitas', 'biaya'])
            ->where('tahun_pelaksanaan', $selectedYear)
            ->where('skala_pelaksanaan', 'Pusat') 
            ->where('skema', $centerId); // Filter otomatis sesuai Role

        // Search Logic
        if ($search) {
            $baseQuery->where(function($q) use ($search) {
                $q->whereHas('identitas', fn($sub) => $sub->where('judul', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // 4. DATA PER TAB
        $data = [
            // Tab Menunggu: Status 2 (Menunggu Kapus)
            'menunggu' => (clone $baseQuery)->where('status_progress', 2)
                ->orderBy('updated_at', 'asc') // FIFO (First In First Out)
                ->paginate($perPage, ['*'], 'page_m')
                ->withQueryString(),

            // Tab Disetujui: Status > 2 (Lolos Kapus, Warek, Selesai) & Tidak Ditolak
            'disetujui' => (clone $baseQuery)->where('status_progress', '>', 2)
                ->where('status_progress', '!=', 99)
                ->orderByDesc('updated_at')
                ->paginate($perPage, ['*'], 'page_d')
                ->withQueryString(),

            // Tab Ditolak: Status 99
            'ditolak' => (clone $baseQuery)->where('status_progress', 99)
                ->orderByDesc('updated_at')
                ->paginate($perPage, ['*'], 'page_t')
                ->withQueryString(),
        ];

        return view('kepala_pusat.kepala_pusat_validasi', compact('data', 'years', 'selectedYear', 'perPage'));
    }

    public function updateKeputusan(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);
        $user = Auth::user();

        // VALIDASI AKSES
        $centerId = (int) filter_var($user->role, FILTER_SANITIZE_NUMBER_INT);
        if ($proposal->skala_pelaksanaan != 'Pusat' || $proposal->skema != $centerId) {
            return back()->with('error', 'Anda tidak memiliki akses validasi proposal ini.');
        }

        // VALIDASI STATUS (Harus 2 / Menunggu Kapus)
        if ($proposal->status_progress != 2) {
            return back()->with('error', 'Gagal: Status proposal sudah berubah.');
        }

        $request->validate([
            'keputusan' => 'required|in:terima,tolak',
            'feedback'  => 'required_if:keputusan,tolak' 
        ]);

        try {
            DB::transaction(function () use ($request, $proposal) {
                if ($request->keputusan == 'terima') {
                    // SETUJU -> STATUS 3 (Validasi Warek)
                    $proposal->update(['status_progress' => 3]);
                } else {
                    // TOLAK -> STATUS 99
                    $proposal->update([
                        'status_progress' => 99, 
                        'feedback' => $request->feedback
                    ]);
                }
            });

            $pesan = $request->keputusan == 'terima' ? 'Proposal berhasil disetujui.' : 'Proposal telah ditolak.';
            return redirect()->route('kepala_pusat.validasi')->with('success', $pesan);

        } catch (\Exception $e) {
            return back()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}