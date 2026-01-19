<?php

namespace App\Http\Controllers;

use App\Models\Proposal;
use App\Services\NotificationFlowService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProposalController extends Controller
{
    /**
     * STATUS PROGRESS MAPPING:
     * 0  = Draft / Menunggu Anggota
     * 1  = Menunggu Validasi Wakil Dekan 3
     * 2  = Menunggu Validasi Pusat (Hanya Skema Pusat)
     * 3  = Menunggu Validasi Wakil Rektor 3
     * 4  = Disetujui / Selesai
     * 99 = Ditolak
     */

    /**
     * Fungsi untuk menaikkan status (Approve)
     */
    public function approve(Request $request, $id)
    {
        $proposal = Proposal::findOrFail($id);
        $user = Auth::user();
        $currentStatus = $proposal->status_progress;

        DB::beginTransaction();
        try {
            // -----------------------------------------------------------------
            // TAHAP 1: VALIDASI OLEH WAKIL DEKAN 3
            // -----------------------------------------------------------------
            if ($currentStatus == 1) {
                // Security Check
                if ($user->role !== 'Wakil Dekan 3') {
                    return response()->json(['success' => false, 'message' => 'Anda tidak memiliki akses.'], 403);
                }

                if ($proposal->skala_pelaksanaan == 'Prodi') {
                    // Jika Prodi: Skip Pusat, langsung ke Warek 3 (Status 3)
                    $proposal->status_progress = 3;
                } else {
                    // Jika Pusat: Lanjut ke Pusat (Status 2)
                    $proposal->status_progress = 2;
                }
                
                $proposal->save();
                
                // Trigger Notif: Wadek Approved
                NotificationFlowService::wadekApproved($proposal);
            }

            // -----------------------------------------------------------------
            // TAHAP 2: VALIDASI OLEH KEPALA PUSAT (Khusus Skema Pusat)
            // -----------------------------------------------------------------
            elseif ($currentStatus == 2) {
                // Security Check: Pastikan User adalah Kepala Pusat yang sesuai
                // Asumsi: Role user di database formatnya "Kepala Pusat 1", "Kepala Pusat 2", dst.
                $requiredRole = 'Kepala Pusat ' . $proposal->skema; 
                
                if ($user->role !== $requiredRole && $user->role !== 'Admin') { // Admin bisa bypass jika perlu
                    return response()->json(['success' => false, 'message' => 'Anda bukan Kepala Pusat yang sesuai untuk skema ini.'], 403);
                }

                // Lanjut ke Warek 3 (Status 3)
                $proposal->status_progress = 3;
                $proposal->save();

                // Trigger Notif: Pusat Approved
                NotificationFlowService::pusatApproved($proposal);
            }

            // -----------------------------------------------------------------
            // TAHAP 3: VALIDASI OLEH WAKIL REKTOR 3 (Final)
            // -----------------------------------------------------------------
            elseif ($currentStatus == 3) {
                // Security Check
                if ($user->role !== 'Wakil Rektor 3') {
                    return response()->json(['success' => false, 'message' => 'Hanya Wakil Rektor 3 yang dapat memvalidasi.'], 403);
                }

                // Finish (Status 4)
                $proposal->status_progress = 4;
                $proposal->save();

                // Trigger Notif: Warek Approved (Proposal Selesai)
                NotificationFlowService::warekApproved($proposal);
            }

            // -----------------------------------------------------------------
            // SUDAH FINAL
            // -----------------------------------------------------------------
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Proposal sudah mencapai tahap akhir atau status tidak valid.'
                ], 400);
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proposal berhasil divalidasi.',
                'data'    => $proposal
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Error Approve Proposal: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Fungsi untuk menolak (Reject)
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'feedback' => 'required|string|max:1000'
        ]);

        $proposal = Proposal::findOrFail($id);
        $user = Auth::user();

        // Validasi Hak Akses Reject (Hanya role yang sedang bertugas yang boleh menolak)
        $canReject = false;
        if ($proposal->status_progress == 1 && $user->role == 'Wakil Dekan 3') $canReject = true;
        if ($proposal->status_progress == 2 && str_contains($user->role, 'Kepala Pusat')) $canReject = true;
        if ($proposal->status_progress == 3 && $user->role == 'Wakil Rektor 3') $canReject = true;

        if (!$canReject) {
            return response()->json(['success' => false, 'message' => 'Anda tidak berwenang menolak proposal pada tahap ini.'], 403);
        }

        DB::beginTransaction();
        try {
            $proposal->status_progress = 99; // Kode Ditolak
            $proposal->feedback = $request->feedback;
            $proposal->save();

            // Trigger Notif Penolakan
            NotificationFlowService::proposalRejected($proposal, $request->feedback, $user->role);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proposal berhasil ditolak.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menolak proposal.'
            ], 500);
        }
    }
}