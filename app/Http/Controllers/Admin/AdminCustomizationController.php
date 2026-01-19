<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Setting;
use App\Models\Skema; 
use App\Models\Skala;
use App\Models\Prodi;
use App\Models\Fakultas;
use App\Models\FakultasProdi;
use App\Models\Jabatan;


class AdminCustomizationController extends Controller
{
    // 1. MAIN HUB
    public function index()
    {
        return view('admin.customization.index');
    }

    // 2. GENERAL SETTINGS PAGE (Previous logic moved here)
    public function general()
    {
        $settings = Setting::all();
        return view('admin.customization.general', compact('settings'));
    }

    // 3. SKEMA SETTINGS PAGE (New)
    public function skema()
    {
        // Fetch all Skalas with their Skemas
        $skalas = Skala::with('skemas')->get(); 
        return view('admin.customization.skema', compact('skalas'));
    }

    public function storeSkala(Request $request)
    {
        $request->validate(['nama' => 'required|unique:skalas,nama']);
        Skala::create($request->all());
        return back()->with('success', 'Skala Baru Berhasil Ditambahkan');
    }

    public function storeSkema(Request $request)
    {
        $request->validate([
            'skala_id' => 'required|exists:skalas,id',
            'label_dropdown' => 'required',
            'nama' => 'required'
        ]);
        Skema::create($request->all());
        return back()->with('success', 'Skema Berhasil Ditambahkan');
    }
    public function updateSkala(Request $request, $id)
    {
        $request->validate(['nama' => 'required|unique:skalas,nama,'.$id]);
        
        $skala = Skala::findOrFail($id);
        $skala->update(['nama' => $request->nama]);
        
        return back()->with('success', 'Nama Skala berhasil diperbarui.');
    }

    public function destroySkala($id)
    {
        // Hapus Skala (Skema di dalamnya otomatis terhapus jika di migration pakai cascade)
        Skala::destroy($id);
        return back()->with('success', 'Skala dan seluruh isinya berhasil dihapus.');
    }

    // --- LOGIKA SKEMA (ITEM) ---

    public function updateSkema(Request $request, $id)
    {
        $request->validate([
            'label_dropdown' => 'required',
            'nama' => 'required'
        ]);

        $skema = Skema::findOrFail($id);
        $skema->update([
            'label_dropdown' => $request->label_dropdown,
            'nama' => $request->nama
        ]);

        return back()->with('success', 'Data Skema berhasil diperbarui.');
    }

    // 5. DELETE SKEMA
    public function destroySkema($id)
    {
        Skema::destroy($id);
        return back()->with('success', 'Skema berhasil dihapus');
    }
    // ==========================================
    // 3. PRODI SETTINGS (NEW SECTION)
    // ==========================================

    public function prodi()
    {
        // Ambil semua data prodi untuk ditampilkan di tabel
        $prodis = Prodi::all();
        return view('admin.customization.prodi', compact('prodis'));
    }

    public function storeProdi(Request $request)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        
        Prodi::create([
            'nama' => $request->nama
        ]);

        return back()->with('success', 'Program Studi berhasil ditambahkan.');
    }

    public function updateProdi(Request $request, $id)
    {
        $request->validate(['nama' => 'required|string|max:255']);
        
        $prodi = Prodi::findOrFail($id);
        $prodi->update([
            'nama' => $request->nama
        ]);

        return back()->with('success', 'Program Studi berhasil diperbarui.');
    }

    public function destroyProdi($id)
    {
        Prodi::destroy($id);
        return back()->with('success', 'Program Studi berhasil dihapus.');
    }

    // ==========================================
    // PROFILE ATTRIBUTES (Fakultas, Prodi Profil, Jabatan)
    // ==========================================

    public function profileAttributes()
    {
        // Ambil Fakultas beserta anak-anak Prodinya
        $fakultas = Fakultas::with('prodis')->get();
        $jabatans = Jabatan::all();
        
        return view('admin.customization.profile_attributes', compact('fakultas', 'jabatans'));
    }

    // --- LOGIC FAKULTAS ---
    public function storeFakultas(Request $request)
    {
        $request->validate(['nama' => 'required|string']);
        Fakultas::create(['nama' => $request->nama]);
        return back()->with('success', 'Fakultas berhasil ditambahkan.');
    }
    
    public function destroyFakultas($id)
    {
        Fakultas::destroy($id);
        return back()->with('success', 'Fakultas beserta prodinya berhasil dihapus.');
    }

    // --- LOGIC PRODI (ANAKNYA FAKULTAS) ---
    public function storeFakultasProdi(Request $request)
    {
        $request->validate([
            'fakultas_id' => 'required|exists:fakultas,id',
            'nama' => 'required|string'
        ]);
        
        FakultasProdi::create([
            'fakultas_id' => $request->fakultas_id,
            'nama' => $request->nama
        ]);
        
        return back()->with('success', 'Prodi berhasil ditambahkan ke Fakultas tersebut.');
    }

    public function destroyFakultasProdi($id)
    {
        FakultasProdi::destroy($id);
        return back()->with('success', 'Prodi berhasil dihapus dari Fakultas.');
    }

    // --- LOGIC JABATAN ---
    public function storeJabatan(Request $request)
    {
        $request->validate(['nama' => 'required|string']);
        Jabatan::create(['nama' => $request->nama]);
        return back()->with('success', 'Jabatan berhasil ditambahkan.');
    }

    public function destroyJabatan($id)
    {
        Jabatan::destroy($id);
        return back()->with('success', 'Jabatan berhasil dihapus.');
    }
}