<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueComment;
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

        $comment->load('user:id,name');

        return response()->json([
            'message' => 'Comment added successfully.',
            'data' => $comment,
        ], 201);
    }
}
