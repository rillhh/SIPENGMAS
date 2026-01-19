<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Proposal;
use App\Models\ProposalLampiran;
use Barryvdh\DomPDF\Facade\Pdf;

class DosenDetailProposalController extends Controller
{
    public function detail($id)
    {
        // 1. Ambil Proposal dengan Eloquent + Relasi
        $detailProposal = Proposal::with([
            'identitas', 
            'biaya', 
            'atribut', 
            'skemaRef',
            'user', 
            'anggotaDosen', 
            'anggotaMahasiswa'
        ])->where('id', $id)->first();

        if (!$detailProposal) {
            return redirect()->back()->with('error', 'Proposal tidak ditemukan.');
        }

        // 2. Data Ketua (Dari Relasi User)
        $ketua = (object) [
            'nama'  => $detailProposal->user->name ?? '-',
            'nidn'  => $detailProposal->user->nidn ?? '-',
            'peran' => 'Ketua Pengusul',
            'tipe'  => 'Ketua',
            'is_approved_dosen' => 1
        ];

        // 3. Gabung Anggota
        $anggota = collect([$ketua])
            ->merge($detailProposal->anggotaDosen)
            ->merge($detailProposal->anggotaMahasiswa);

        // 4. Cek Status Undangan untuk User yang Login
        $userLogin = Auth::user();
        
        // Cari apakah user login ada di daftar anggota dosen proposal ini
        $myInvitation = $detailProposal->anggotaDosen
                            ->where('nidn', $userLogin->nidn)
                            ->first();
        
        $statusUndanganSaya = $myInvitation ? $myInvitation->is_approved_dosen : null;
        $idUndanganSaya     = $myInvitation ? $myInvitation->id : null;

        // 5. Ambil Lampiran
        $lampiran = ProposalLampiran::where('proposal_id', $id)->get();

        // 6. Return View (PERBAIKAN TYPO DISINI)
        // Pastikan file ada di: resources/views/dosen/detail_proposal.blade.php
        return view('dosen.dosen_detail_proposal', compact(
            'detailProposal', 'anggota', 'lampiran', 'statusUndanganSaya', 'idUndanganSaya'
        ));
    }

    // ==========================================
    // FUNGSI EXPORT PDF
    // ==========================================
    public function exportPdf($id)
    {
        // Gunakan Eloquent untuk konsistensi
        $detailProposal = Proposal::with([
            'identitas', 'biaya', 'atribut', 'user', 'pengesahan', 'anggotaDosen', 'anggotaMahasiswa'
        ])->findOrFail($id);

        // Setup Data untuk PDF
        $ketua = (object) [
            'nama' => $detailProposal->user->name ?? '-',
            'nidn' => $detailProposal->user->nidn ?? '-',
            'prodi' => $detailProposal->user->prodi ?? '-',
            'jabatan_fungsional' => $detailProposal->user->jabatan_fungsional ?? '-'
        ];

        // Generate PDF
        $pdf = Pdf::loadView('dosen.dosen_unduh_pengesahan', [
            'p'         => $detailProposal,
            'ketua'     => $ketua,
            'dosen'     => $detailProposal->anggotaDosen,
            'mahasiswa' => $detailProposal->anggotaMahasiswa
        ]);

        $pdf->setPaper('a4', 'portrait');

        return $pdf->download('Lembar_Pengesahan_' . time() . '.pdf');
    }
}