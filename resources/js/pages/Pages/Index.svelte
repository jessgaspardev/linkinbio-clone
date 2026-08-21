<script lang="ts">
import { useForm, router, Link } from '@inertiajs/svelte';
import * as pagesRoutes from '@/routes/pages';

type PageRecord = { id: string; title: string; slug: string; theme: string };

let { pages, username, subscribed }: { pages: PageRecord[]; username: string; subscribed: boolean } = $props();

const form = useForm({ title: '' });

function submit() {
    form.post(pagesRoutes.store().url, {
        onSuccess: () => form.reset(),
    });
}

let drafts = $state<Record<string, { title: string; slug: string }>>({});
let fieldErrors = $state<Record<string, { title?: string; slug?: string }>>({});
let saving = $state<Record<string, boolean>>({});

function setDraftField(page: PageRecord, field: 'title' | 'slug', value: string) {
    if (!drafts[page.id]) {
        drafts[page.id] = { title: page.title, slug: page.slug };
    }

    drafts[page.id][field] = value;
}

function isDirty(page: PageRecord): boolean {
    const draft = drafts[page.id];
    
    return !!draft && (draft.title !== page.title || draft.slug !== page.slug);
}

function savePage(page: PageRecord) {
    const draft = drafts[page.id] ?? { title: page.title, slug: page.slug };
    saving[page.id] = true;

    router.patch(pagesRoutes.update({ page: page.id }).url, {
        title: draft.title,
        slug: draft.slug,
    }, {
        onError: (errors) => {
            fieldErrors[page.id] = { title: errors.title, slug: errors.slug };
        },
        onSuccess: () => {
            fieldErrors[page.id] = {};
        },
        onFinish: () => {
            saving[page.id] = false;
        },
    });
}

function cancelEdits(page: PageRecord) {
    drafts[page.id] = { title: page.title, slug: page.slug };
    fieldErrors[page.id] = {};
}

function destroyPage(page: PageRecord) {
    if (!confirm(`Delete "${page.title}"? This can't be undone.`)) {
        return;
    }

    router.delete(pagesRoutes.destroy({ page: page.id }).url);
}
</script>

<button
    onclick={() => router.post(subscribed ? '/billing-portal' : '/subscribe')}
    class="btn btn-primary btn-sm"
>
    {subscribed ? 'Manage billing' : 'Upgrade to Pro'}
</button>
    <div class="mx-auto max-w-2xl">
        <h1 class="font-display text-3xl font-bold text-base-content">Your pages</h1>
        <p class="mt-1 text-sm text-base-content/70">Every link-in-bio page you've built.</p>

        <form
            onsubmit={(e) => { 
                e.preventDefault(); submit(); 
                }}
            class="mt-8 flex gap-2"
        >
            <input
                type="text"
                bind:value={form.title}
                placeholder="New page title"
                class="input input-bordered flex-1 bg-base-200 text-base-content"
            />
            <button type="submit" class="btn btn-primary" disabled={form.processing}>
                Add page
            </button>
        </form>
        {#if form.errors.title}
            <p class="mt-1 text-sm text-error">{form.errors.title}</p>
        {/if}

        <ul class="mt-8 flex flex-col gap-3">
            {#each pages as page (page.id)}
                <li class="card bg-base-200 p-4">
                    <div class="flex items-center justify-between gap-4">
                        <input
                            type="text"
                            value={drafts[page.id]?.title ?? page.title}
                            oninput={(e) => setDraftField(page, 'title', e.currentTarget.value)}
                            class="input input-bordered flex-1 bg-base-100 text-base-content"
                        />
                        <Link href={pagesRoutes.show({ page: page.id }).url} class="btn btn-ghost btn-sm">
                            Manage links
                        </Link>
                        <button
                            onclick={() => destroyPage(page)}
                            class="btn btn-error btn-outline btn-sm"
                        >
                            Delete
                        </button>
                    </div>
                    {#if fieldErrors[page.id]?.title}
                        <p class="mt-1 text-xs text-error">{fieldErrors[page.id].title}</p>
                    {/if}

                    <div class="mt-3 flex items-center gap-1 font-mono text-xs text-base-content/60">
                        <span>/{username}/</span>
                        <input
                            type="text"
                            value={drafts[page.id]?.slug ?? page.slug}
                            oninput={(e) => setDraftField(page, 'slug', e.currentTarget.value)}
                            class="input input-bordered input-xs flex-1 bg-base-100 font-mono text-base-content"
                        />
                    </div>
                    {#if fieldErrors[page.id]?.slug}
                        <p class="mt-1 text-xs text-error">{fieldErrors[page.id].slug}</p>
                    {/if}

                    {#if isDirty(page)}
                        <div class="mt-3 flex gap-2">
                            <button
                                onclick={() => savePage(page)}
                                class="btn btn-primary btn-sm"
                                disabled={saving[page.id]}
                            >
                                {saving[page.id] ? 'Saving...' : 'Save'}
                            </button>
                            <button onclick={() => cancelEdits(page)} class="btn btn-ghost btn-sm">
                                Cancel
                            </button>
                        </div>
                    {/if}
                </li>
            {:else}
                <li class="text-sm text-base-content/60">No pages yet — add your first one above.</li>
            {/each}
        </ul>
    </div>
