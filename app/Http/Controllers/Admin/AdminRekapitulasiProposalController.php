<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\ProposalLampiran;
use App\Exports\RekapProposalExport;
use Maatwebsite\Excel\Facades\Excel;

class AdminRekapitulasiProposalController extends Controller
{   
    /**
     * Menampilkan halaman rekapitulasi proposal.
     */
    public function index(Request $request)
    {
        // 1. Setup Tahun & Filter
        $startYear = 2019;
        $currentYear = date('Y');
        $years = range($currentYear + 1, $startYear); // Urutan Descending (2026, 2025, dst)
        
        $selectedYear = $request->input('year', $currentYear);
        $perPage = $request->input('per_page', 10);
        $search = $request->input('search');

        // 2. Query Dasar (Gunakan Model & Eager Loading)
        $query = Proposal::with(['user', 'identitas', 'biaya']) 
                         ->where('tahun_pelaksanaan', $selectedYear);

        // 3. Logika Pencarian
        if ($search) {
            $query->where(function($q) use ($search) {
                $q->whereHas('identitas', fn($sub) => $sub->where('judul', 'like', "%{$search}%"))
                  ->orWhereHas('user', fn($sub) => $sub->where('name', 'like', "%{$search}%"));
            });
        }

        // 4. Data per Tab (Menggunakan Status Angka yang Konsisten)
        $data = [
            // Tab 1: SEDANG PROSES (Status 0, 1, 2, 3)
            'proses' => (clone $query)->where('status_progress', '<', 4)
                                      ->where('status_progress', '!=', 99) 
                                      ->latest()
                                      ->paginate($perPage, ['*'], 'page_p')
                                      ->withQueryString(),

            // Tab 2: DIDANAI (Status 4)
            'didanai'  => (clone $query)->where('status_progress', 4) 
                                        ->latest()
                                        ->paginate($perPage, ['*'], 'page_d')
                                        ->withQueryString(),

            // Tab 3: DITOLAK (Status 99)
            'ditolak'  => (clone $query)->where('status_progress', 99) 
                                        ->latest()
                                        ->paginate($perPage, ['*'], 'page_t')
                                        ->withQueryString(),
        ];

        return view('admin.admin_rekapitulasi_proposal', compact('data', 'years', 'selectedYear'));
    }

    /**
     * Menampilkan detail proposal (Read-Only / Action).
     */
    public function detail($id)
    {
        // 1. Gunakan Eloquent dengan Relasi Lengkap
        $detailProposal = Proposal::with([
            'identitas', 
            'biaya', 
            'atribut', 
            'user',
            'anggotaDosen', 
            'anggotaMahasiswa'
        ])->findOrFail($id);

        // 2. Susun Data Tim
        $ketua = (object) [
            'nama'  => $detailProposal->user->name ?? '-',
            'peran' => 'Ketua Pengusul',
            'tipe'  => 'Ketua'
        ];

        $anggota = collect([$ketua])
            ->merge($detailProposal->anggotaDosen)
            ->merge($detailProposal->anggotaMahasiswa);

        // 3. Ambil Lampiran
        $lampiran = ProposalLampiran::where('proposal_id', $id)->get();

        // 4. Return View (Gunakan view detail admin yang baru kita rapihkan sebelumnya)
        return view('admin.admin_detail_proposal', compact('detailProposal', 'anggota', 'lampiran'));
    }

    /**
     * Memproses keputusan Admin (Jika Admin punya hak veto/edit status).
     */
    public function updateKeputusan(Request $request, $id)
    {
        $request->validate([
            'status_keputusan' => 'required|in:Didanai,Ditolak',
            'feedback'         => 'required_if:status_keputusan,Ditolak', 
        ]);

        $proposal = Proposal::findOrFail($id);

        if ($request->status_keputusan == 'Didanai') {
            $proposal->update(['status_progress' => 4]); // 4 = Selesai/Didanai
        } else {
            $proposal->update([
                'status_progress' => 99, // 99 = Ditolak
                'feedback' => $request->feedback
            ]);
        }

        return redirect()->back()->with('success', 'Status proposal berhasil diperbarui.');
    }
    public function exportExcel(Request $request)
    {
        $year = $request->input('year', date('Y'));
        $status = $request->input('status', 'proses'); 

        $fileName = 'Rekapitulasi_Proposal_' . ucfirst($status) . '_' . $year . '.xlsx';
        
        return Excel::download(new RekapProposalExport($year, $status), $fileName);
    }
}