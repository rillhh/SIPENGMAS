<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\DatabaseMessage;

class DekanUploadSignatureNotification extends Notification
{
    use Queueable;

    public function __construct()
    {
        //
    }

    public function via($notifiable)
    {
        return ['database']; // Simpan ke tabel 'notifications'
    }

    public function toArray($notifiable)
    {
        return [
            'title' => 'Permintaan Tanda Tangan',
            'message' => 'Wakil Dekan 3 memerlukan tanda tangan digital Anda untuk memvalidasi proposal.',
            'url' => route('profile.edit'), // Link ke halaman profil Dekan
            'icon' => 'bi-pen-fill',
            'color' => 'danger'
        ];
    }
}