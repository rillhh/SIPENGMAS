<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    // Method untuk menandai semua sudah dibaca
    public function markAllRead()
    {
        Auth::user()->unreadNotifications->markAsRead();
        return redirect()->back();
    }

    // Method untuk membaca satu notif dan redirect (Logic yang kita bahas sebelumnya)
    public function readAndRedirect($id)
    {
        $notification = Auth::user()->notifications()->find($id);

        if ($notification) {
            // 1. Tandai sudah dibaca
            $notification->markAsRead();

            // 2. Cek apakah ada proposal_id (untuk direct link ke detail)
            if (isset($notification->data['proposal_id'])) {
                // Pastikan route 'dosen.detail_proposal' sudah ada
                return redirect()->route('dosen.detail_proposal', $notification->data['proposal_id']);
            }

            // 3. Fallback ke URL yang disimpan di notif atau dashboard
            $targetUrl = $notification->data['url'] ?? route('dosen.dashboard');
            return redirect($targetUrl);
        }

        return back();
    }
}