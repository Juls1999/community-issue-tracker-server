<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\IssueCategory;

class IssueCategoryController extends Controller
{
    public function index()
    {
        return response()->json(
            IssueCategory::query()
                ->where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'description'])
        );
    }
}
