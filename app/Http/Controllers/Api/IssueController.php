<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;


class IssueController extends Controller
{
    public function index(Request $request)
    {
        $this->authorize('viewAny', Issue::class);

        $user = $request->user();
        $role = $user->role?->name;

        // Base query with role-based access control
        $query = Issue::query();

        if ($role === 'resident') {
            $query->where('user_id', $user->id);

        } elseif ($role === 'staff') {
            $query->where(function ($query) use ($user) {
                $query->where('assigned_to', $user->id);

                if ($user->barangay_id !== null) {
                    $query->orWhere(function ($query) use ($user) {
                        $query->whereNull('assigned_to')
                            ->where('barangay_id', $user->barangay_id);
                    });
                }
            });

        } elseif ($role !== 'admin') {
            abort(403, 'You are not authorized to view issues.');
        }

        // Statistics across ALL accessible issues
        $statistics = (clone $query)->getStatistics();

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->input('search');

            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%")
                    ->orWhere('uuid', 'like', "%{$search}%")
                    ->orWhereHas('category', function ($categoryQuery) use ($search) {
                        $categoryQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply status filter
        if ($request->filled('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        // Paginate results
        $issues = $query
            ->with(['category', 'barangay'])
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return IssueResource::collection($issues)
            ->additional([
                'statistics' => $statistics,
            ]);
    }

    public function store(Request $request)
    {
        $this->authorize('create', Issue::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['required', 'string', 'max:10000'],

            'issue_category_id' => [
                'required',
                Rule::exists('issue_categories', 'id')
                    ->where('is_active', true),
            ],

            'barangay_id' => [
                'nullable',
                Rule::exists('barangays', 'id')
                    ->where('is_active', true),
            ],

            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],

            'latitude' => [
                'nullable',
                'numeric',
                'between:-90,90',
            ],

            'longitude' => [
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ]);

        $issue = Issue::create([
            ...$validated,
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'status' => 'pending',
        ]);

        $issue->load(['user', 'assignee', 'category', 'barangay']);

        return (new IssueResource($issue))
            ->response()
            ->setStatusCode(201);
    }

    public function show(Issue $issue)
    {
        $this->authorize('view', $issue);

        $issue->load([
            'user',
            'assignee',
            'category',
            'barangay',
        ]);

        return new IssueResource($issue);
    }


    public function update(Request $request, Issue $issue)
    {
        $this->authorize('update', $issue);

        $validated = $request->validate([
            'title' => ['sometimes', 'required', 'string', 'max:255'],
            'description' => [
                'sometimes',
                'required',
                'string',
                'max:10000',
            ],
            'issue_category_id' => [
                'sometimes',
                'required',
                Rule::exists('issue_categories', 'id')
                    ->where('is_active', true),
            ],
            'priority' => [
                'sometimes',
                Rule::in(['low', 'medium', 'high', 'urgent']),
            ],
            'latitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-90,90',
            ],
            'longitude' => [
                'sometimes',
                'nullable',
                'numeric',
                'between:-180,180',
            ],
        ]);

        if ($request->user()->role?->name === 'resident') {
            unset($validated['priority']);
        }

        $issue->update($validated);

        $issue->load(['user', 'assignee', 'category', 'barangay']);

        return new IssueResource($issue);
    }


    public function assign(Request $request, Issue $issue)
    {
        $this->authorize('assign', $issue);

        $validated = $request->validate([
            'assigned_to' => [
                'required',
                'integer',
                'exists:users,id',
            ],
        ]);

        $staff = User::with('role')->findOrFail(
            $validated['assigned_to']
        );

        if ($staff->role?->name !== 'staff') {
            throw ValidationException::withMessages([
                'assigned_to' => [
                    'The selected user must have the staff role.',
                ],
            ]);
        }

        if (
            $issue->barangay_id !== null
            && $staff->barangay_id !== $issue->barangay_id
        ) {
            throw ValidationException::withMessages([
                'assigned_to' => [
                    'Staff must belong to the issue barangay.',
                ],
            ]);
        }

        $issue->update([
            'assigned_to' => $staff->id,
        ]);

        $issue->load(['user', 'assignee', 'category', 'barangay']);

        return new IssueResource($issue);
    }


    public function updateStatus(Request $request, Issue $issue)
    {
        $this->authorize('updateStatus', $issue);

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['pending', 'in_progress', 'resolved']),
            ],
        ]);

        $current = $issue->status;
        $next = $validated['status'];
        $isAdmin = $request->user()->role?->name === 'admin';

        $allowedTransitions = [
            'pending' => ['in_progress'],
            'in_progress' => ['resolved'],
            'resolved' => [],
        ];

        $allowed = $allowedTransitions[$current] ?? [];

        // Admins may reopen a resolved issue.
        if ($isAdmin && $current === 'resolved') {
            $allowed[] = 'in_progress';
        }

        if ($current !== $next && !in_array($next, $allowed, true)) {
            throw ValidationException::withMessages([
                'status' => [
                    "The status cannot change from {$current} to {$next}.",
                ],
            ]);
        }

        DB::transaction(function () use ($issue, $next) {
            $issue->status = $next;
            $issue->resolved_at = $next === 'resolved'
                ? ($issue->resolved_at ?? now())
                : null;
            $issue->save();
        });

        $issue->load(['user', 'assignee', 'category', 'barangay']);

        return new IssueResource($issue);
    }


}
