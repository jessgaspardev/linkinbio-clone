<?php

namespace App\Http\Controllers;

use App\Models\Page;
use Inertia\Inertia;

class PublicPageController extends Controller
{
    public function show(string $username, string $slug)
    {
        $page = Page::whereHas('user', fn ($query) => $query->where('username', $username))
            ->where('slug', $slug)
            ->where('is_public', true)
            ->firstOrFail();

        return Inertia::render('Public/PageShow', [
            'username' => $username,
            'page' => [
                'title' => $page->title,
                'slug' => $page->slug,
                'theme' => $page->theme,
            ],
            'links' => $page->links->map(fn ($link) => [
                'id' => $link->id,
                'label' => $link->label,
                'url' => $link->url,
            ]),
        ]);
    }
}