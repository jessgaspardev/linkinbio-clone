<script lang="ts">
import { useForm, router, Link } from '@inertiajs/svelte';
import { dragHandleZone, dragHandle } from 'svelte-dnd-action';
import LinkController from '@/actions/App/Http/Controllers/LinkController';
import PageController from '@/actions/App/Http/Controllers/PageController';
import * as pagesRoutes from '@/routes/pages';

type PageRecord = { id: string; title: string; slug: string; theme: string; is_public: boolean };
type LinkRecord = { id: string; page_id: string; label: string; url: string; position: number };

let { page, links, availableThemes }: { page: PageRecord; links: LinkRecord[]; availableThemes: Record<string, string> } = $props();

let togglingVisibility = $state(false);

function toggleVisibility() {
    togglingVisibility = true;
    router.patch(PageController.toggleVisibility({ page: page.id }).url, {}, {
        onFinish: () => { 
            togglingVisibility = false; 
        },
    });
}

let settingTheme = $state(false);

function setTheme(theme: string) {
    settingTheme = true;
    router.patch(PageController.setTheme({ page: page.id }).url, { theme }, {
        onFinish: () => { 
            settingTheme = false; 
        },
    });
}

const form = useForm({ label: '', url: '' });

function submit() {
    form.post(LinkController.store({ page: page.id }).url, {
        onSuccess: () => form.reset(),
    });
}

// Local, reorderable copy of the links list. A writable $derived: it tracks
// `links` automatically, but stays directly assignable during a drag — the
// override sticks until `links` changes again, then the derivation retakes over.
let linksList = $derived([...links]);

let reorderError = $state<string | null>(null);

function handleConsider(e: CustomEvent<{ items: LinkRecord[] }>) {
    linksList = e.detail.items;
}

function handleFinalize(e: CustomEvent<{ items: LinkRecord[] }>) {
    linksList = e.detail.items;
    saveOrder();
}

function saveOrder() {
    const linkIds = linksList.map((l) => l.id);

    router.patch(LinkController.reorder({ page: page.id }).url, { link_ids: linkIds }, {
        preserveScroll: true,
        onError: () => {
            reorderError = "Couldn't save the new order — reloading.";
            router.reload({ onFinish: () => { 
                reorderError = null; 
            } });
        },
    });
}

let drafts = $state<Record<string, { label: string; url: string }>>({});
let fieldErrors = $state<Record<string, { label?: string; url?: string }>>({});
let saving = $state<Record<string, boolean>>({});

function setDraftField(link: LinkRecord, field: 'label' | 'url', value: string) {
    if (!drafts[link.id]) {
        drafts[link.id] = { label: link.label, url: link.url };
    }

    drafts[link.id][field] = value;
}

function isDirty(link: LinkRecord): boolean {
    const draft = drafts[link.id];

    return !!draft && (draft.label !== link.label || draft.url !== link.url);
}

function saveLink(link: LinkRecord) {
    const draft = drafts[link.id] ?? { label: link.label, url: link.url };
    saving[link.id] = true;

    router.patch(LinkController.update({ page: page.id, link: link.id }).url, {
        label: draft.label,
        url: draft.url,
    }, {
        onError: (errors) => {
            fieldErrors[link.id] = { label: errors.label, url: errors.url };
        },
        onSuccess: () => {
            fieldErrors[link.id] = {};
        },
        onFinish: () => {
            saving[link.id] = false;
        },
    });
}

function cancelEdits(link: LinkRecord) {
    drafts[link.id] = { label: link.label, url: link.url };
    fieldErrors[link.id] = {};
}

function destroyLink(link: LinkRecord) {
    if (!confirm(`Delete the "${link.label}" link? This can't be undone.`)) {
        return;
    }

    router.delete(LinkController.destroy({ page: page.id, link: link.id }).url);
}
</script>

<div class="min-h-screen bg-base-100 px-6 py-10">
    <div class="mx-auto max-w-2xl">
        <Link href={pagesRoutes.index().url} class="text-sm text-base-content/60 hover:underline">
            &larr; Back to pages
        </Link>

        <h1 class="font-display mt-2 text-3xl font-bold text-base-content">{page.title}</h1>
        <p class="mt-1 font-mono text-sm text-base-content/60">/{page.slug}</p>

        <button
            onclick={toggleVisibility}
            disabled={togglingVisibility}
            class="btn btn-sm mt-3 {page.is_public ? 'btn-primary' : 'btn-outline'}"
        >
            {page.is_public ? 'Public' : 'Private'}
        </button>

        <div class="mt-3 flex flex-wrap gap-2">
            {#each Object.entries(availableThemes) as [key, label] (key)}
                <button
                    onclick={() => setTheme(key)}
                    disabled={settingTheme}
                    class="btn btn-sm {page.theme === key ? 'btn-primary' : 'btn-outline'}"
                >
                    {label}
                </button>
            {/each}
        </div>

        <form
            onsubmit={(e) => { 
                e.preventDefault(); submit(); 
                }}
            class="mt-8 flex flex-col gap-2 sm:flex-row"
        >
            <input
                type="text"
                bind:value={form.label}
                placeholder="Link label"
                class="input input-bordered bg-base-200 text-base-content sm:w-40"
            />
            <input
                type="text"
                bind:value={form.url}
                placeholder="https://..."
                class="input input-bordered flex-1 bg-base-200 text-base-content"
            />
            <button type="submit" class="btn btn-primary" disabled={form.processing}>
                Add link
            </button>
        </form>
        {#if form.errors.label}
            <p class="mt-1 text-sm text-error">{form.errors.label}</p>
        {/if}
        {#if form.errors.url}
            <p class="mt-1 text-sm text-error">{form.errors.url}</p>
        {/if}

        {#if reorderError}
            <p class="mt-4 text-sm text-error">{reorderError}</p>
        {/if}

        <ul
            use:dragHandleZone={{ items: linksList, flipDurationMs: 200 }}
            onconsider={handleConsider}
            onfinalize={handleFinalize}
            class="mt-8 flex flex-col gap-3"
        >
            {#each linksList as link (link.id)}
                <li class="card bg-base-200 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                        <div
                            use:dragHandle
                            aria-label="Reorder {link.label}"
                            class="touch-none cursor-grab px-1 text-base-content/50"
                        >
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor">
                                <circle cx="5" cy="3" r="1.3" />
                                <circle cx="11" cy="3" r="1.3" />
                                <circle cx="5" cy="8" r="1.3" />
                                <circle cx="11" cy="8" r="1.3" />
                                <circle cx="5" cy="13" r="1.3" />
                                <circle cx="11" cy="13" r="1.3" />
                            </svg>
                        </div>
                        <input
                            type="text"
                            value={drafts[link.id]?.label ?? link.label}
                            oninput={(e) => setDraftField(link, 'label', e.currentTarget.value)}
                            class="input input-bordered bg-base-100 text-base-content sm:w-40"
                        />
                        <input
                            type="text"
                            value={drafts[link.id]?.url ?? link.url}
                            oninput={(e) => setDraftField(link, 'url', e.currentTarget.value)}
                            class="input input-bordered flex-1 bg-base-100 font-mono text-sm text-base-content"
                        />
                        <button
                            onclick={() => destroyLink(link)}
                            class="btn btn-error btn-outline btn-sm"
                        >
                            Delete
                        </button>
                    </div>
                    {#if fieldErrors[link.id]?.label}
                        <p class="mt-1 text-xs text-error">{fieldErrors[link.id].label}</p>
                    {/if}
                    {#if fieldErrors[link.id]?.url}
                        <p class="mt-1 text-xs text-error">{fieldErrors[link.id].url}</p>
                    {/if}

                    {#if isDirty(link)}
                        <div class="mt-3 flex gap-2">
                            <button
                                onclick={() => saveLink(link)}
                                class="btn btn-primary btn-sm"
                                disabled={saving[link.id]}
                            >
                                {saving[link.id] ? 'Saving...' : 'Save'}
                            </button>
                            <button onclick={() => cancelEdits(link)} class="btn btn-ghost btn-sm">
                                Cancel
                            </button>
                        </div>
                    {/if}
                </li>
            {:else}
                <li class="text-sm text-base-content/60">No links yet — add your first one above.</li>
            {/each}
        </ul>
    </div>
</div>