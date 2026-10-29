<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Issue;
use App\Models\IssueAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class IssueAttachmentController extends Controller
{
    public function index(Issue $issue)
    {
        $this->authorize('view', $issue);

        return response()->json(
            $issue->attachments()
                ->with('uploader:id,name')
                ->latest()
                ->paginate(20)
        );
    }


    public function store(Request $request, Issue $issue)
    {
        $this->authorize('view', $issue);

        $validated = $request->validate([
            'images' => ['required', 'array', 'min:1'],
            'images.*' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $disk = Storage::disk('local');
        $paths = [];

        try {
            $attachments = DB::transaction(function () use ($validated, $request, $issue, $disk, &$paths) {
                $attachments = [];

                foreach ($validated['images'] as $file) {
                    $path = $file->store('issue-attachments', 'local');
                    $paths[] = $path;

                    $attachments[] = $issue->attachments()->create([
                        'uploaded_by' => $request->user()->id,
                        'file_path' => $path,
                        'original_name' => $file->getClientOriginalName(),
                        'mime_type' => $file->getMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }

                return $attachments;
            });
        } catch (\Throwable $exception) {
            foreach ($paths as $path) {
                $disk->delete($path);
            }

            throw $exception;
        }

        foreach ($attachments as $attachment) {
            $attachment->load('uploader:id,name');
        }

        return response()->json([
            'message' => 'Photos uploaded successfully.',
            'data' => $attachments,
        ], 201);
    }


    public function download(
        Issue $issue,
        IssueAttachment $attachment
    ) {
        $this->authorize('view', $issue);

        abort_unless(
            $attachment->issue_id === $issue->id,
            404
        );

        abort_unless(
            Storage::disk('local')->exists($attachment->file_path),
            404
        );

        return Storage::disk('local')->download(
            $attachment->file_path,
            basename($attachment->original_name)
        );
    }
}
