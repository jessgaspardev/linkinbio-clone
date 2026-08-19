<script lang="ts">
import { useForm, router, Link } from '@inertiajs/svelte';
import LinkController from '@/actions/App/Http/Controllers/LinkController';
import * as pagesRoutes from '@/routes/pages';

type PageRecord = { id: string; title: string; slug: string; theme: string };
type LinkRecord = { id: string; page_id: string; label: string; url: string; position: number };

let { page, links }: { page: PageRecord; links: LinkRecord[] } = $props();

const form = useForm({ label: '', url: '' });

function submit() {
    form.post(LinkController.store({ page: page.id }).url, {
        onSuccess: () => form.reset(),
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

        <ul class="mt-8 flex flex-col gap-3">
            {#each links as link (link.id)}
                <li class="card bg-base-200 p-4">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
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