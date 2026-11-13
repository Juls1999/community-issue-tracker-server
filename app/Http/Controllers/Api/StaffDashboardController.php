<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\IssueResource;
use App\Models\Issue;
use Illuminate\Http\Request;

class StaffDashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        abort_unless(
            $user->role?->name === 'staff',
            403,
            'Unauthorized access.'
        );

        abort_if(
            !$user->barangay_id,
            403,
            'Your account is not assigned to a barangay.'
        );

        // Issues visible to this staff member.
        $issues = Issue::query()
            ->where('barangay_id', $user->barangay_id)
            ->where(function ($query) use ($user) {
                $query->where('assigned_to', $user->id)
                    ->orWhereNull('assigned_to');
            });

        $statistics = [
            'total' => (clone $issues)->count(),
            'pending' => (clone $issues)->where('status', 'pending')->count(),
            'inProgress' => (clone $issues)->where('status', 'in_progress')->count(),
            'resolved' => (clone $issues)->where('status', 'resolved')->count(),
        ];

        $recentIssues = (clone $issues)
            ->with(['user', 'assignee', 'category', 'barangay'])
            ->latest()
            ->limit(5)
            ->get();

        return response()->json([
            'data' => [
                'statistics' => $statistics,
                'recentIssues' => IssueResource::collection($recentIssues),
            ],
        ]);
    }
}