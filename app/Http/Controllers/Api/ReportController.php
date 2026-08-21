<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function store(Request $request, string $username, string $slug)
    {
        $page = Page::whereHas('user', fn ($query) => $query->where('username', $username))
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();
        
        $validated = $request->validate([
            'reason' => 'nullable|string|max:1000',
        ]);

        $page->reports()->create([
            'reason' => $validated['reason'] ?? null,
        ]);

        return response()->json(['message' => 'Report submitted.'], 201);
    }
}
