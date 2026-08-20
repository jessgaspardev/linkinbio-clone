<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class GalleryController extends Controller
{
    public function index(Request $request) 
    {
        $validated = $request->validate([
            'search' => 'nullable|string|max:255',
        ]);

        $pages = Page::where('is_public', true)
            ->where('is_listed', true)
            ->when($validated['search'] ?? null, function ($query, $search) {
                $query->where('title', 'like', '%'.$search.'%');
            })
            ->with('user:id,username')
            ->latest()
            ->paginate(12)
            ->through(fn ($page) => [
                'username' => $page->user->username,
                'slug' => $page->slug,
                'title' => $page->title
            ]);
        return response()->json($pages);    
    }
}
