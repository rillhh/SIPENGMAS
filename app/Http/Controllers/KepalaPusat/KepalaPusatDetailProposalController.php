<?php

namespace App\Http\Controllers\KepalaPusat;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Models\Proposal;
use App\Models\ProposalLampiran;

class KepalaPusatDetailProposalController extends Controller
{
    public function show($id)
    {
        // 1. Identifikasi User & Pusat Studi
        $user = Auth::user();
        
        // Asumsi Role 'Kepala Pusat 1', ambil angka 1-nya
        $centerId = 0;
        $skemas = Skema::all();
        
        foreach ($skemas as $skema) {
            if ($skema->label_dropdown && stripos($user->name, $skema->label_dropdown) !== false) {
                $centerId = $skema->id;
                break;
            }
        }
        if ($centerId == 0) $centerId = (int) filter_var($user->role, FILTER_SANITIZE_NUMBER_INT);

        // 2. Ambil Proposal dengan Relasi (Eloquent)
        $detailProposal = Proposal::with([
            'identitas', 'biaya', 'atribut', 'user','skemaRef',
            'anggotaDosen', 'anggotaMahasiswa'
        ])->where('id', $id)->first();

        if (!$detailProposal) {
            return redirect()->back()->with('error', 'Proposal tidak ditemukan.');
        }

        // 3. KEAMANAN: Pastikan Kepala Pusat hanya melihat proposal Pusat-nya sendiri
        if ($detailProposal->skala_pelaksanaan != 'Pusat' || $detailProposal->skema != $centerId) {
             return redirect()->route('kepala_pusat.validasi')->with('error', 'Anda tidak memiliki akses ke proposal ini.');
        }

        // 4. Data Ketua
        $ketua = (object) [
            'nama'  => $detailProposal->user->name ?? '-',
            'peran' => 'Ketua Pengusul',
            'tipe'  => 'Ketua'
        ];

        // 5. Gabung Tim (Dosen + Mahasiswa)
        $anggota = collect([$ketua])
            ->merge($detailProposal->anggotaDosen)
            ->merge($detailProposal->anggotaMahasiswa);

        // 6. Ambil Lampiran
        $lampiran = ProposalLampiran::where('proposal_id', $id)->get();

        return view('kepala_pusat.kepala_pusat_detail_proposal', compact('detailProposal', 'anggota', 'lampiran'));
    }
}