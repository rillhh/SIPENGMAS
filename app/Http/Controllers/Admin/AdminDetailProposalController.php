<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class AdminDetailProposalController extends Controller
{
    public function show($id)
    {
        // 1. Gunakan Eloquent Model dengan Eager Loading
        $detailProposal = Proposal::with([
            'identitas', 
            'biaya', 
            'atribut', 
            'user',
            'anggotaDosen', 
            'anggotaMahasiswa'
        ])->findOrFail($id);

        // 2. Susun Data Tim (Menggunakan Collection Laravel)
        // Ketua diambil dari relasi User
        $ketua = (object) [
            'nama'  => $detailProposal->user->name ?? '-',
            'peran' => 'Ketua Pengusul',
            'tipe'  => 'Ketua'
        ];

        // Anggota diambil dari relasi yang sudah di-load
        $anggota = collect([$ketua])
            ->merge($detailProposal->anggotaDosen)
            ->merge($detailProposal->anggotaMahasiswa);

        // 3. Ambil Lampiran (Bisa pakai Model juga biar konsisten)
        $lampiran = ProposalLampiran::where('proposal_id', $id)->get();

        return view('admin.admin_detail_proposal', compact('detailProposal', 'anggota', 'lampiran'));
    }
}