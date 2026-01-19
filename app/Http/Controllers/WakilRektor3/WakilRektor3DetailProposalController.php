<?php

namespace App\Http\Controllers\WakilRektor3;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class WakilRektor3DetailProposalController extends Controller
{
    public function show($id)
    {
        // 1. QUERY ELOQUENT (Relasi Lengkap)
        $detailProposal = Proposal::with([
            'identitas', 
            'biaya', 
            'atribut', 
            'user', 
            'anggotaDosen', 
            'anggotaMahasiswa'
        ])->where('id', $id)->first();

        if (!$detailProposal) {
            // Pastikan route 'wakil_rektor.validasi' sudah ada di web.php
            return redirect()->route('wakil_rektor.validasi')->with('error', 'Proposal tidak ditemukan.');
        }

        // 2. DATA KETUA
        $ketua = (object) [
            'nama'  => $detailProposal->user->name ?? '-',
            'peran' => 'Ketua Pengusul',
            'tipe'  => 'Ketua'
        ];

        // 3. GABUNG ANGGOTA (Dosen + Mahasiswa)
        $anggota = collect([$ketua])
            ->merge($detailProposal->anggotaDosen)
            ->merge($detailProposal->anggotaMahasiswa);

        // 4. LAMPIRAN
        $lampiran = ProposalLampiran::where('proposal_id', $id)->get();

        // 5. RETURN VIEW
        // Pastikan nama folder view sesuai (wakil_rektor3)
        return view('wakil_rektor3.wakil_rektor3_detail_proposal', compact('detailProposal', 'anggota', 'lampiran'));
    }
}