<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueComment;
use App\Notifications\IssueCommented;
use Illuminate\Http\Request;

class IssueCommentController extends Controller
{
    public function index(Issue $issue)
    {
        $this->authorize('view', $issue);

        $comments = $issue->comments()
            ->where('is_internal', false)
            ->with('user:id,name')
            ->latest()
            ->paginate(20);

        return response()->json($comments);
    }

    public function store(Request $request, Issue $issue)
    {
        $this->authorize('view', $issue);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:5000'],
        ]);

        $comment = $issue->comments()->create([
            'user_id' => $request->user()->id,
            'body' => $validated['body'],
            'is_internal' => false,
        ]);

        // Load the relationships required by the notification.
        $comment->load([
            'user:id,name',
            'issue:id,uuid,title,user_id,assigned_to',
        ]);

        $commenter = $request->user();

        // Determine who should receive the notification.
        $recipient = match ($commenter->role?->name) {
            'resident' => $issue->assignee,
            'staff', 'admin' => $issue->user,
            default => null,
        };

        // Prevent self-notifications.
        if (
            $recipient &&
            $recipient->id !== $commenter->id &&
            !$comment->is_internal
        ) {
            $recipient->notify(
                new IssueCommented($comment)
            );
        }

        return response()->noContent(201);
    }
}
