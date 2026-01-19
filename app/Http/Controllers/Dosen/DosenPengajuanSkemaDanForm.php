<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use App\Models\Skema; 
use App\Models\Skala;
use App\Models\Prodi;

class DosenPengajuanSkemaDanForm extends Controller
{
    /**
     * PAGE 1: Choose Year & Skema
     * Now fetches data from Database instead of hardcoded JS.
     */
    public function showSkema()
    {
        // Fetch all Skalas with their active Skemas
        $allData = Skala::with(['skemas' => function($q) {
            $q->where('is_active', 1);
        }])->get();

        // Format for JS: Key = Skala Name, Value = Array of Skemas
        $skemaList = [];
        foreach($allData as $s) {
            $skemaList[$s->nama] = $s->skemas->map(function($item) {
                return [
                    'id' => $item->id,
                    'text' => $item->label_dropdown,
                    'nama' => $item->nama
                ];
            });
        }

        // Pass the list of Skala Names for the first dropdown
        $skalaOptions = $allData->pluck('nama');

        return view('dosen.dosen_pengajuan_skema', compact('skemaList', 'skalaOptions'));
    }

    /**
     * PAGE 2: Fill the Proposal Form
     * Now finds the Scheme Name by ID from Database.
     */
    public function showForm($year, $skemaId, $role)
    {
        $skema = Skema::find($skemaId);

        // Fallback if skema is deleted/not found
        $namaSkema = $skema ? $skema->nama : 'Skema Tidak Ditemukan / Non-Aktif';

        $prodis = Prodi::all();

        return view('dosen.dosen_pengajuan_proposal', [
            'skemaId' => $skemaId,
            'selectedYear' => $year,
            'selectedRole' => $role,
            'namaSkema' => $namaSkema,
            'prodis' => $prodis,
        ]);
    }
}