<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest; // Ensure you use this or standard Request
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage; // Import Storage
use Illuminate\View\View;
use App\Models\Fakultas;
use App\Models\Jabatan;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        // 1. Ambil Data Fakultas beserta Prodinya (untuk Dropdown & Script JS)
        $fakultasDB = Fakultas::with('prodis')->get();

        // 2. Ambil Data Jabatan (untuk Dropdown)
        $jabatans = Jabatan::all();

        // 3. Format Data untuk JavaScript (JSON)
        // Agar script di Blade bisa baca: "Nama Fakultas" => ["Prodi A", "Prodi B"]
        $fakultasData = [];
        foreach($fakultasDB as $f) {
            // Kita ambil nama prodi dari relasi 'prodis'
            $fakultasData[$f->nama] = $f->prodis->pluck('nama');
        }

        // 4. Kirim semua variabel ke View
        return view('profile.edit', [
            'user' => $request->user(),
            'fakultas' => $fakultasDB,       
            'jabatans' => $jabatans,         
            'fakultasData' => $fakultasData, 
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        // 1. Validasi Input
        // Added validation for 'tanda_tangan'
        $validated = $request->validate([
            'fakultas'           => ['required', 'string', 'max:100'],
            'prodi'              => ['required', 'string', 'max:100'],
            'jabatan_fungsional' => ['required', 'string', 'max:100'],
            'tanda_tangan'       => ['nullable', 'image', 'mimes:png,jpg,jpeg', 'max:2048'], // Max 2MB
        ]);

        $user = $request->user();
        
        // Exclude tanda_tangan from simple fill, handle it separately
        $userData = collect($validated)->except(['tanda_tangan'])->toArray();
        $user->fill($userData);

        // --- LOGIKA UPLOAD TANDA TANGAN (AUTO TRANSPARAN & RESIZE) ---
        if ($request->hasFile('tanda_tangan')) {
            $file = $request->file('tanda_tangan');

            // 1. Hapus file lama jika ada
            if ($user->tanda_tangan && Storage::exists('public/' . $user->tanda_tangan)) {
                Storage::delete('public/' . $user->tanda_tangan);
            }

            // 2. Tentukan path penyimpanan
            // Kita akan menyimpan manual menggunakan GD, jadi kita butuh absolute path
            $filename = 'tanda_tangan/' . uniqid() . '.png'; // Force PNG for transparency
            $absolutePath = storage_path('app/public/' . $filename);
            
            // Pastikan folder ada
            if (!file_exists(dirname($absolutePath))) {
                mkdir(dirname($absolutePath), 0777, true);
            }

            // 3. Proses Gambar (Resize & Transparansi)
            $processed = $this->convertWhiteToTransparent($file->getPathname(), $absolutePath);

            if ($processed) {
                $user->tanda_tangan = $filename;
            } else {
                // Fallback jika GD gagal, simpan biasa
                $path = $file->store('tanda_tangan', 'public');
                $user->tanda_tangan = $path;
            }
        }
        // -----------------------------------------------------------

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return back()->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);
        $user = $request->user();
        Auth::logout();
        $user->delete();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return Redirect::to('/');
    }

    /**
     * Helper: Resize image if > 400px width and convert white background to transparent.
     */
    /**
     * Helper: Resize image & Convert white background to transparent (Improved Logic)
     */
    private function convertWhiteToTransparent($sourcePath, $destinationPath)
    {
        if (!extension_loaded('gd')) {
            return false;
        }

        $info = getimagesize($sourcePath);
        $mime = $info['mime'];

        // 1. Load Gambar
        switch ($mime) {
            case 'image/jpeg':
            case 'image/jpg':
                $img = imagecreatefromjpeg($sourcePath);
                break;
            case 'image/png':
                $img = imagecreatefrompng($sourcePath);
                break;
            default:
                return false;
        }

        // 2. Siapkan Ukuran Baru (Resize)
        $originalWidth = imagesx($img);
        $originalHeight = imagesy($img);
        $targetWidth = 400; // Lebar optimal untuk PDF

        if ($originalWidth > $targetWidth) {
            $ratio = $targetWidth / $originalWidth;
            $targetHeight = (int) ($originalHeight * $ratio);
        } else {
            $targetWidth = $originalWidth;
            $targetHeight = $originalHeight;
        }

        // 3. Buat Kanvas Baru (True Color)
        $newImg = imagecreatetruecolor($targetWidth, $targetHeight);

        // PENTING: Matikan blending agar kita bisa memanipulasi alpha channel secara langsung
        imagealphablending($newImg, false);
        imagesavealpha($newImg, true);

        // 4. Isi kanvas dengan Transparan penuh terlebih dahulu
        $transparent = imagecolorallocatealpha($newImg, 255, 255, 255, 127);
        imagefill($newImg, 0, 0, $transparent);

        // 5. Resize gambar asli ke kanvas sementara untuk diproses
        // Kita butuh kanvas sementara agar resize tidak merusak transparansi target
        $tempImg = imagecreatetruecolor($targetWidth, $targetHeight);
        imagecopyresampled($tempImg, $img, 0, 0, 0, 0, $targetWidth, $targetHeight, $originalWidth, $originalHeight);
        imagedestroy($img); // Hapus gambar asli dari memori

        // 6. LOOPING PIXEL (Algoritma Penghapus Background)
        // Threshold: 0-255. 
        // Angka 200 artinya: semua warna yang kecerahannya di atas 200 (putih/abu-abu muda) akan dihapus.
        // Jika masih ada background abu-abu, TURUNKAN angkanya (misal jadi 180).
        $threshold = 200; 

        for ($x = 0; $x < $targetWidth; $x++) {
            for ($y = 0; $y < $targetHeight; $y++) {
                $rgb = imagecolorat($tempImg, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;

                // Hitung Kecerahan (Brightness)
                // Rumus mata manusia: 0.299R + 0.587G + 0.114B
                $brightness = ($r * 0.299) + ($g * 0.587) + ($b * 0.114);

                if ($brightness > $threshold) {
                    // Jika TERANG (Kertas), biarkan transparan (jangan digambar apa-apa karena kanvas sudah transparan)
                    // Atau kita set eksplisit pixel ini jadi transparan:
                    imagesetpixel($newImg, $x, $y, $transparent);
                } else {
                    // Jika GELAP (Tinta), salin warnanya
                    // Opsional: Jika ingin tinta jadi lebih hitam pekat, uncomment baris bawah:
                    // $color = imagecolorallocatealpha($newImg, 0, 0, 0, 0); // Hitam pekat
                    
                    // Gunakan warna asli:
                    $color = imagecolorallocatealpha($newImg, $r, $g, $b, 0); // 0 = Tidak Transparan
                    imagesetpixel($newImg, $x, $y, $color);
                }
            }
        }

        // 7. Simpan Hasil
        $result = imagepng($newImg, $destinationPath);

        // Bersihkan Memori
        imagedestroy($tempImg);
        imagedestroy($newImg);

        return $result;
    }

    /**
     * Handle AJAX Signature Upload from Proposal Form
     */
    public function uploadSignatureAjax(Request $request)
    {
        $request->validate([
            'tanda_tangan' => ['required', 'image', 'mimes:png,jpg,jpeg', 'max:2048'],
        ]);

        $user = $request->user();

        try {
            if ($request->hasFile('tanda_tangan')) {
                $file = $request->file('tanda_tangan');

                // 1. Hapus lama
                if ($user->tanda_tangan && Storage::exists('public/' . $user->tanda_tangan)) {
                    Storage::delete('public/' . $user->tanda_tangan);
                }

                // 2. Simpan Path
                $filename = 'tanda_tangan/' . uniqid() . '.png';
                $absolutePath = storage_path('app/public/' . $filename);

                if (!file_exists(dirname($absolutePath))) {
                    mkdir(dirname($absolutePath), 0777, true);
                }

                // 3. Proses (Gunakan fungsi helper yang sudah ada di controller ini)
                $processed = $this->convertWhiteToTransparent($file->getPathname(), $absolutePath);

                if ($processed) {
                    $user->tanda_tangan = $filename;
                } else {
                    $path = $file->store('tanda_tangan', 'public');
                    $user->tanda_tangan = $path;
                }

                $user->save();

                return response()->json(['success' => true, 'message' => 'Tanda tangan berhasil disimpan.']);
            }
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Terjadi kesalahan server: ' . $e->getMessage()], 500);
        }

        return response()->json(['success' => false, 'message' => 'File tidak ditemukan.'], 400);
    }
}