<?php

namespace App\Http\Controllers\Dekan;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class DekanDetailProposalController extends Controller
{
    /**
     * Menampilkan Detail Proposal untuk Monitoring Dekan
     */
    public function show($id)
    {
        // 1. QUERY DATA (Eager Loading Relasi)
        $detailProposal = Proposal::with([
            'identitas', 
            'biaya', 
            'atribut', 
            'user', 
            'anggotaDosen', 
            'anggotaMahasiswa'
        ])
        ->where('id', $id)
        ->where('skala_pelaksanaan', 'Prodi') // SECURITY: Hanya Proposal Skala Prodi
        ->first();

        // Jika tidak ditemukan atau bukan skala prodi, kembalikan
        if (!$detailProposal) {
            return redirect()->route('dekan.monitoring')
                ->with('error', 'Proposal tidak ditemukan atau tidak berada dalam lingkup monitoring Fakultas.');
        }

        // 2. SUSUN DATA KETUA (Manual Object)
        $ketua = (object) [
            'nama'  => $detailProposal->user->name ?? '-',
            'peran' => 'Ketua Pengusul',
            'tipe'  => 'Ketua'
        ];

        // 3. GABUNGKAN DATA TIM (Ketua + Dosen + Mahasiswa)
        $anggota = collect([$ketua])
            ->merge($detailProposal->anggotaDosen)
            ->merge($detailProposal->anggotaMahasiswa);

        // 4. AMBIL LAMPIRAN (Dokumen & Luaran)
        $lampiran = ProposalLampiran::where('proposal_id', $id)->get();

        // 5. RETURN VIEW
        return view('dekan.dekan_detail_proposal', compact('detailProposal', 'anggota', 'lampiran'));
    }
}