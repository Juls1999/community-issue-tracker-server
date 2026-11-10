<?php

namespace App\Notifications;

use App\Models\IssueComment;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class IssueCommented extends Notification
{
    use Queueable;

    public function __construct(
        public IssueComment $comment,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'issue_commented',
            'issue_uuid' => $this->comment->issue->uuid,
            'issue_title' => $this->comment->issue->title,
            'comment_id' => $this->comment->id,
            'commenter_name' => $this->comment->user->name,
            'message' => "{$this->comment->user->name} commented on your report \"{$this->comment->issue->title}\".",
        ];
    }
}
