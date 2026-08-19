<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Page;
use Illuminate\Http\Request;

class LinkController extends Controller
{
    protected function linkRules(): array
    {
        return [
            'label' => 'required|string|max:255',
            'url' => ['required', 'string', 'max:2048', 'regex:/^https?:\/\//i'],
        ];
    }

    public function store(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate($this->linkRules());

        $position = ($page->links()->max('position') ?? 0) + 1;

        $page->links()->create([
            'label' => $validated['label'],
            'url' => $validated['url'],
            'position' => $position,
        ]);

        return redirect()->back();
    }

    public function update(Request $request, Page $page, Link $link)
    {
        $this->authorize('update', $page);
        abort_unless($link->page_id === $page->id, 404);

        $link->update($request->validate($this->linkRules()));

        return redirect()->back();
    }

    public function destroy(Page $page, Link $link)
    {
        $this->authorize('update', $page);
        abort_unless($link->page_id === $page->id, 404);

        $link->delete();

        return redirect()->back();
    }
}