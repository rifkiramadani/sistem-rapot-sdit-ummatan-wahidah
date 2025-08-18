<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use Illuminate\Notifications\Messages\BroadcastMessage;

class ReportFinished extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public int $reportId,
        public string $title = 'Report Finished',
        public string $body = 'Your sales report is ready to download.',
        public ?string $href = null,
    ) {
        $this->href ??= "/reports/{$reportId}";
    }

    public function via($notifiable): array
    {
        // Store + broadcast (real-time)
        return ['database', 'broadcast'];
    }

    public function toArray($notifiable): array
    {
        return [
            'title' => $this->title,
            'body'  => $this->body,
            'href'  => $this->href,
        ];
    }

    public function toBroadcast($notifiable): BroadcastMessage
    {
        // Anything returned here arrives on the frontend as `notification` payload
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
