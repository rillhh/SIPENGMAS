<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Exception;
use App\Models\ProposalCoreAnggotaDosen;
use App\Models\Proposal;
use App\Services\NotificationFlowService;

class DosenKonfirmasiAnggotaController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();

        // 1. SETUP FILTER (Tahun, Search, Pagination)
        $currentYear = date('Y');
        $years = range($currentYear + 1, 2019); // Descending (2026 -> 2019)
        $selectedYear = $request->input('year', $currentYear);
        $perPage = in_array($request->input('per_page'), [5, 10, 50]) ? $request->input('per_page') : 10;
        $search = $request->input('search');

        // 2. QUERY BASE (Milik Dosen Ini)
        $baseQuery = ProposalCoreAnggotaDosen::with([
            'proposal.user', 
            'proposal.identitas', 
            'proposal.biaya'
        ])->where('nidn', $user->nidn);

        // Filter: Tahun & Search
        $baseQuery->whereHas('proposal', function ($q) use ($selectedYear, $search) {
            $q->where('tahun_pelaksanaan', $selectedYear);
            
            if ($search) {
                $q->whereHas('identitas', fn($sq) => $sq->where('judul', 'like', "%{$search}%"));
            }
        });

        // 3. LOGIKA DATA PER TAB
        // Menggunakan Logic Status Angka yang Konsisten
        $data = [
            // TAB 1: UNDANGAN MASUK (Anggota Belum Acc, Proposal Masih Draft/0)
            'invitations' => (clone $baseQuery)
                ->where('is_approved_dosen', 0)
                ->whereHas('proposal', fn($q) => $q->where('status_progress', 0))
                ->latest()
                ->paginate($perPage, ['*'], 'page_inv')
                ->withQueryString(),

            // TAB 2: MENUNGGU TIM (Anggota Sudah Acc, Tapi Proposal Masih Draft/0)
            'waiting' => (clone $baseQuery)
                ->where('is_approved_dosen', 1)
                ->whereHas('proposal', fn($q) => $q->where('status_progress', 0))
                ->latest('updated_at')
                ->paginate($perPage, ['*'], 'page_wait')
                ->withQueryString(),

            // TAB 3: PROSES REVIEW (Proposal Sedang Jalan: Status 1, 2, 3)
            // Berlaku untuk Prodi & Pusat (Wadek, Pusat, Warek)
            'process' => (clone $baseQuery)
                ->where('is_approved_dosen', 1)
                ->whereHas('proposal', function ($q) {
                    $q->whereBetween('status_progress', [1, 3]);
                })
                ->latest('updated_at')
                ->paginate($perPage, ['*'], 'page_proc')
                ->withQueryString(),

            // TAB 4: SELESAI / DITOLAK (Status 4 atau 99)
            'finished' => (clone $baseQuery)
                ->where('is_approved_dosen', 1) // Pastikan user sudah join
                ->whereHas('proposal', function ($q) {
                    $q->whereIn('status_progress', [4, 99]);
                })
                ->latest('updated_at')
                ->paginate($perPage, ['*'], 'page_fin')
                ->withQueryString(),
        ];

        return view('dosen.dosen_konfirmasi_anggota', compact('data', 'years', 'selectedYear', 'perPage'));
    }

    public function terima($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $anggota = ProposalCoreAnggotaDosen::findOrFail($id);

                // Security Check
                if ($anggota->nidn !== Auth::user()->nidn) {
                    throw new Exception('Unauthorized action.');
                }

                $proposal = Proposal::findOrFail($anggota->proposal_id);

                // Hanya bisa terima jika proposal masih Draft (0)
                if ($proposal->status_progress != 0) {
                    throw new Exception('Gagal: Proposal sudah diproses atau dibatalkan.');
                }

                // Update Status Anggota
                $anggota->update(['is_approved_dosen' => 1]);

                // Cek apakah SEMUA anggota sudah setuju?
                $pendingCount = ProposalCoreAnggotaDosen::where('proposal_id', $proposal->id)
                    ->where('is_approved_dosen', 0)
                    ->count();

                // Jika semua setuju -> Maju ke Wadek (Status 1)
                if ($pendingCount == 0) {
                    $proposal->update(['status_progress' => 1]);
                    
                    // Kirim Notifikasi (Jika Service Ada)
                    if (class_exists(NotificationFlowService::class)) {
                        NotificationFlowService::anggotaApproved($proposal);
                    }
                }
            });

            return back()->with('success', 'Berhasil bergabung dengan proposal.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function tolak($id)
    {
        try {
            $anggota = ProposalCoreAnggotaDosen::findOrFail($id);

            if ($anggota->nidn !== Auth::user()->nidn) {
                throw new Exception('Unauthorized action.');
            }

            DB::transaction(function () use ($anggota) {
                // Jika satu anggota menolak, proposal BATAL (99)
                Proposal::where('id', $anggota->proposal_id)->update(['status_progress' => 99]);
                
                // Hapus data undangan (Opsional, tergantung aturan bisnis)
                // $anggota->delete(); 
                
                // Atau tandai sebagai 'Ditolak' di tabel pivot jika ada kolom status_invitation
                // Disini kita biarkan delete() sesuai kode asli Anda
                $anggota->delete();
            });

            return back()->with('success', 'Undangan ditolak. Proposal dibatalkan.');
        } catch (Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }
}