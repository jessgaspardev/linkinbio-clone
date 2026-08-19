<?php

namespace App\Http\Controllers;

use App\Models\Link;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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

    public function reorder(Request $request, Page $page)
    {
        $this->authorize('update', $page);

        $validated = $request->validate([
            'link_ids' => 'required|array',
            'link_ids.*' => 'required|string',
        ]);

        $linkIds = $validated['link_ids'];

        // Every id in the payload must actually belong to this page — never
        // trust the client to only send ids it's allowed to touch.
        $ownedCount = $page->links()->whereIn('id', $linkIds)->count();
        abort_unless($ownedCount === count($linkIds), 404);

        DB::transaction(function () use ($linkIds) {
            foreach ($linkIds as $index => $id) {
                Link::where('id', $id)->update(['position' => $index + 1]);
            }
        });

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