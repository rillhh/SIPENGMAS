<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Notifications\Messages\BroadcastMessage;

class NotifProposal extends Notification implements ShouldBroadcast
{
    use Queueable;

    public $dataNotif;

    public function __construct($dataNotif)
    {
        $this->dataNotif = $dataNotif;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    /**
     * SIMPAN KE DATABASE (PENTING UNTUK CONTROLLER REDIRECT)
     */
    public function toArray($notifiable)
    {
        return [
            'title'   => $this->dataNotif['title'] ?? 'Notifikasi Baru',
            'message' => $this->dataNotif['pesan'],
            // Simpan proposal_id ke database (kolom data)
            'proposal_id' => $this->dataNotif['proposal_id'] ?? null, 
            'url'     => $this->dataNotif['url'] ?? '#',
            'icon'    => $this->dataNotif['icon'] ?? 'fas fa-bell',
        ];
    }

    /**
     * KIRIM SINYAL REALTIME (Agar JS bisa menangkap ID nya juga)
     */
    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'title'       => $this->dataNotif['title'] ?? 'Notifikasi Baru',
            'message'     => $this->dataNotif['pesan'], 
            'proposal_id' => $this->dataNotif['proposal_id'] ?? null, // Kirim ID via socket
            'url'         => $this->dataNotif['url'] ?? '#',
            'icon'        => $this->dataNotif['icon'] ?? 'fas fa-bell',
            'time'        => now()->diffForHumans(),
        ]);
    }
}