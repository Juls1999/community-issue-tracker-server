<?php

namespace App\Notifications;

use App\Models\Issue;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueStatusUpdated extends Notification
{
    use Queueable;

    public function __construct(
        public Issue $issue,
        public string $oldStatus,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'issue_status_updated',
            'issue_uuid' => $this->issue->uuid,
            'issue_title' => $this->issue->title,
            'old_status' => $this->oldStatus,
            'new_status' => $this->issue->status,
            'message' => "Your report \"{$this->issue->title}\" status changed from {$this->oldStatus} to {$this->issue->status}.",
        ];
    }
}
