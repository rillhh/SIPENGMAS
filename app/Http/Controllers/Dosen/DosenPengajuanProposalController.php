<?php

namespace App\Http\Controllers\Dosen;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Exception;

// Models
use App\Models\Proposal;
use App\Models\ProposalCoreAtribut;
use App\Models\ProposalCoreUraian;
use App\Models\ProposalCoreAnggotaDosen;
use App\Models\ProposalCoreAnggotaMahasiswa;
use App\Models\ProposalCoreBiaya;
use App\Models\ProposalCorePengesahan;
use App\Models\User;

// SERVICES
use App\Services\NotificationFlowService;

class DosenPengajuanProposalController extends Controller
{
    public function store(Request $request)
    {
        // 1. Validasi Data (Disamakan dengan Blade maxlength)
        $validated = $request->validate([
            'tahun_pelaksanaan' => 'required|integer|min:2019|max:2100',
            'skala_pelaksanaan' => 'required|string|max:50',
            'skema' => 'required|integer',
            'file_proposal' => 'required|file|mimes:pdf,doc,docx|max:10240',

            // Identitas
            'judul' => 'required|string|max:300',
            'abstrak' => 'required|string|max:500',
            'keyword' => 'required|string|max:150',
            'periode_kegiatan' => 'required|integer',
            'bidang_fokus' => 'required|string|max:50',

            // Atribut & Uraian
            'rumpun_ilmu' => 'required|string|max:50',
            'nama_institusi_mitra' => 'required|string|max:50',
            'penanggung_jawab_mitra' => 'required|string|max:50',
            'alamat_mitra' => 'required|string|max:250', // Max 250

            'objek_pengabdian' => 'required|string|max:50',
            'instansi_terlibat' => 'nullable|string|max:50', // Nullable jika tidak wajib
            'lokasi_pengabdian' => 'required|string|max:250', // Max 250 (Textarea)
            'temuan_ditargetkan' => 'required|string|max:50',

            // Anggota Dosen
            'anggota_dosen' => 'required|array|min:1',
            'anggota_dosen.*.nidn' => 'required|string|max:20',
            'anggota_dosen.*.nama' => 'required|string|max:100', // Nama bisa panjang
            'anggota_dosen.*.prodi_dosen' => 'required|string|max:100', // Prodi bisa panjang
            'anggota_dosen.*.peran' => 'required|string|max:50',

            // Anggota Mahasiswa
            'anggota_mhs' => 'required|array|min:1',
            'anggota_mhs.*.npm' => 'required|string|max:20',
            'anggota_mhs.*.nama' => 'required|string|max:100', // Nama bisa panjang
            'anggota_mhs.*.prodi' => 'required|string|max:100', // Prodi bisa panjang
            'anggota_mhs.*.peran' => 'required|string|max:50',

            // Biaya & Pengesahan
            'honor_output' => 'required|string',
            'belanja_non_operasional' => 'required|string',
            'bahan_habis_pakai' => 'required|string',
            'transportasi' => 'required|string',
            'kota' => 'required|string|max:30',
            'jabatan_mengetahui' => 'required|string|max:50',
            'nama_mengetahui' => 'required|string|max:100', // Nama bisa panjang
            'nip_mengetahui' => 'required|string|max:20',
            'jabatan_menyetujui' => 'required|string|max:50',
            'nama_menyetujui' => 'required|string|max:100', // Nama bisa panjang
            'nip_menyetujui' => 'required|string|max:20',
        ]);

        try {
            DB::beginTransaction();
            
            // Upload File
            $filePath = null;
            if ($request->hasFile('file_proposal')) {
                $file = $request->file('file_proposal');
                $originalName = $file->getClientOriginalName();
                $fileName = time() . '_' . $originalName;
                $filePath = $file->storeAs('proposal_files', $fileName, 'public');
            }

            // 2. Simpan Data Utama Proposal
            $proposal = Proposal::create([
                'user_id' => Auth::id(),
                'tahun_pelaksanaan' => $request->tahun_pelaksanaan,
                'skala_pelaksanaan' => $request->skala_pelaksanaan,
                'skema' => $request->skema,
                'status_progress' => 0, 
                'file_proposal' => $filePath,
            ]);

            // 3. Simpan Tabel Turunan
            $proposal->identitas()->create($request->only(['judul', 'abstrak', 'keyword', 'periode_kegiatan', 'bidang_fokus']));

            ProposalCoreAtribut::create(array_merge(['proposal_id' => $proposal->id], $request->only(['rumpun_ilmu', 'nama_institusi_mitra', 'penanggung_jawab_mitra', 'alamat_mitra'])));

            ProposalCoreUraian::create(array_merge(['proposal_id' => $proposal->id], $request->only(['objek_pengabdian', 'instansi_terlibat', 'lokasi_pengabdian', 'temuan_ditargetkan'])));

            // 4. SIMPAN ANGGOTA DOSEN
            $listAnggotaBerhasilDisimpan = []; 

            foreach ($request->anggota_dosen as $item) {
                $anggotaBaru = ProposalCoreAnggotaDosen::create([
                    'proposal_id' => $proposal->id,
                    'nidn'        => $item['nidn'],
                    'nama'        => $item['nama'],
                    // 'fakultas' => 'Fakultas Teknologi Informasi', // Optional
                    'prodi'       => $item['prodi_dosen'], 
                    'peran'       => $item['peran'],
                    'is_approved_dosen' => 0, 
                ]);
                $listAnggotaBerhasilDisimpan[] = $anggotaBaru;
            }

            // Trigger Notifikasi
            NotificationFlowService::sendInvitation($listAnggotaBerhasilDisimpan, $proposal);

            // 5. Simpan Anggota Mahasiswa
            $mhsData = [];
            foreach ($request->anggota_mhs as $item) {
                $mhsData[] = [
                    'proposal_id' => $proposal->id,
                    'npm'         => $item['npm'],
                    'nama'        => $item['nama'],
                    'prodi'       => $item['prodi'], 
                    'peran'       => $item['peran'],
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ];
            }
            ProposalCoreAnggotaMahasiswa::insert($mhsData);

            // 6. Simpan Biaya
            ProposalCoreBiaya::create([
                'proposal_id' => $proposal->id,
                'honor_output' => (int) str_replace(['.', ','], '', $request->honor_output),
                'belanja_non_operasional' => (int) str_replace(['.', ','], '', $request->belanja_non_operasional),
                'bahan_habis_pakai' => (int) str_replace(['.', ','], '', $request->bahan_habis_pakai),
                'transportasi' => (int) str_replace(['.', ','], '', $request->transportasi),
                'jumlah_tendik' => (int) str_replace(['.', ','], '', $request->jumlah_tendik ?? 0),
            ]);

            // 7. Simpan Pengesahan
            ProposalCorePengesahan::create(array_merge(['proposal_id' => $proposal->id], $request->only(['kota', 'jabatan_mengetahui', 'nama_mengetahui', 'nip_mengetahui', 'jabatan_menyetujui', 'nama_menyetujui', 'nip_menyetujui'])));

            DB::commit();
            return redirect()->route('dosen.dashboard')->with('success', 'Proposal berhasil disimpan! Undangan telah dikirim ke anggota.');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error("Gagal simpan proposal: " . $e->getMessage());
            return back()->withInput()->with('error', 'Terjadi kesalahan sistem: ' . $e->getMessage());
        }
    }
}