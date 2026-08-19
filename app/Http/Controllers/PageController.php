<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        return Inertia::render('Pages/Index', [
            'pages' => auth()->user()->pages()->latest()->get(),
            'username' => auth()->user()->username,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
        ]);

        auth()->user()->pages()->create([
            'title' => $validated['title'],
            'slug' => Page::generateSlug($validated['title'], auth()->id()),
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => Page::slugRules(auth()->id(), $page->id),
        ]);

        $page->update($validated);

        return redirect()->back();
    }

    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);

        $page->delete();

        return redirect()->back();
    }
}