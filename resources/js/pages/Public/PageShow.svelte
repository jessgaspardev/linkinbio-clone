<script lang="ts">
import AppHead from '@/components/AppHead.svelte';

type LinkItem = { id: string; label: string; url: string };
type PageData = { title: string; slug: string; theme: string };

let { username, page, links }: { username: string; page: PageData; links: LinkItem[] } = $props();

let reportStatus = $state<'idle' | 'sending' | 'sent' | 'error'>('idle');

async function reportPage() {
    const reason = prompt("What's wrong with this page? (optional)");

    if (reason === null) {
        return;
    }

    reportStatus = 'sending';

    try {
        const response = await fetch(`/api/${username}/${page.slug}/report`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
            },
            body: JSON.stringify({ reason: reason || null }),
        });

        reportStatus = response.ok ? 'sent' : 'error';
    } catch {
        reportStatus = 'error';
    }
}
</script>

<AppHead title={page.title} />

<div data-theme={page.theme} class="min-h-screen bg-base-100 flex items-center justify-center px-6 py-16">
    <div class="w-full max-w-sm">
        <div class="text-center mb-6">
            <span class="inline-block rounded-full bg-primary px-4 py-1 font-display text-xs font-bold text-primary-content">
                @{username}
            </span>
            <h1 class="font-display mt-3 text-2xl font-bold text-base-content">{page.title}</h1>
        </div>

        <div class="rounded-xl bg-base-200">
            {#each links as link, i (link.id)}
                <a
                    href={link.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    class="flex items-center gap-3 px-5 py-4 {i < links.length - 1 ? 'border-b border-dashed border-base-300' : ''}"
                >
                    <span class="font-mono text-xs font-bold text-primary w-6">
                        {String(i + 1).padStart(2, '0')}
                    </span>
                    <span class="flex-1 font-medium text-base-content">{link.label}</span>
                    <span class="font-mono text-xs text-base-content/50">&#8599;</span>
                </a>
            {:else}
                <p class="px-5 py-8 text-center text-sm text-base-content/60">No links yet.</p>
            {/each}
        </div>

        <p class="mt-6 text-center text-xs text-base-content/40">
            Made with linkinbio
        </p>

        <div class="mt-2 text-center">
            {#if reportStatus === 'sent'}
                <p class="text-xs text-base-content/40">Thanks, this page has been reported.</p>
            {:else}
                <button
                    onclick={reportPage}
                    disabled={reportStatus === 'sending'}
                    class="text-xs text-base-content/30 underline hover:text-base-content/50"
                >
                    Report this page
                </button>
                {#if reportStatus === 'error'}
                    <p class="mt-1 text-xs text-error">Something went wrong — try again later.</p>
                {/if}
            {/if}
        </div>
    </div>
</div>