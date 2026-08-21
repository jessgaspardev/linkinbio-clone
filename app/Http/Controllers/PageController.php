<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PageController extends Controller
{
    public function index()
    {
        return Inertia::render('Pages/Index', [
            'pages' => auth()->user()->pages()->latest()->get(),
            'username' => auth()->user()->username,
            'subscribed' => auth()->user()->subscribed('default'),
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

    public function show(Page $page)
    {
        $this->authorize('view', $page);

        return Inertia::render('Pages/Show', [
            'page' => $page,
            'links' => $page->links,
            'availableThemes' => config('page-themes'),
        ]);
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

    public function toggleVisibility(Page $page)
    {
        $this->authorize('update', $page);

        $isPublic = ! $page->is_public;

        $page->update([
            'is_public' => $isPublic,
            // Going private also un-lists the page — being listed is a bigger
            // promise than being link-shareable, and shouldn't silently
            // survive a trip through "private" without being re-chosen.
            'is_listed' => $isPublic ? $page->is_listed : false,
        ]);

        return redirect()->back();
    }

    public function toggleListed(Page $page)
    {
        $this->authorize('update', $page);

        abort_unless($page->is_public, 422, 'Make the page public before listing it.');

        $page->update(['is_listed' => ! $page->is_listed]);

        return redirect()->back();
    }

    public function setTheme(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'theme' => ['required', 'string', Rule::in(array_keys(config('page-themes')))],
        ]);

        $page->update(['theme' => $validated['theme']]);

        return redirect()->back();
    }

    public function destroy(Page $page)
    {
        $this->authorize('delete', $page);

        $page->delete();

        return redirect()->back();
    }
}