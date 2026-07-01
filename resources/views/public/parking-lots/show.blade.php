<x-layout title="{{ $lot->name }}">
    <div class="mb-4">
        <a href="{{ route('parking-lots.browse') }}" class="text-sm text-slate-600 hover:underline dark:text-slate-400">
            &larr; Back to browse
        </a>
    </div>

    <article class="rounded border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h1 class="text-2xl font-semibold">{{ $lot->name }}</h1>
                @if ($lot->owner)
                    <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                        Managed by {{ $lot->owner->name }}
                    </p>
                @endif
            </div>
            <span class="rounded bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                Verified
            </span>
        </div>

        @if ($lot->description)
            <p class="mt-4 text-sm leading-relaxed text-slate-700 dark:text-slate-300">
                {{ $lot->description }}
            </p>
        @endif

        <dl class="mt-6 grid gap-4 sm:grid-cols-2">
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Hourly rate</dt>
                <dd class="mt-1 text-lg font-semibold">৳{{ number_format((float) $lot->hourly_rate, 2) }}</dd>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Available spots</dt>
                <dd class="mt-1 text-lg font-semibold">
                    {{ (int) $lot->available_spots }} / {{ (int) $lot->total_capacity }}
                </dd>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Latitude</dt>
                <dd class="mt-1 text-sm font-medium">{{ $lot->latitude }}</dd>
            </div>
            <div class="rounded border border-slate-200 bg-slate-50 p-3 dark:border-slate-800 dark:bg-slate-950">
                <dt class="text-xs uppercase tracking-wide text-slate-500 dark:text-slate-400">Longitude</dt>
                <dd class="mt-1 text-sm font-medium">{{ $lot->longitude }}</dd>
            </div>
        </dl>

        <div class="mt-6 flex flex-wrap items-center gap-3">
            @auth
                <button
                    type="button"
                    disabled
                    class="cursor-not-allowed rounded bg-slate-300 px-4 py-2 text-sm font-medium text-slate-600 dark:bg-slate-700 dark:text-slate-300"
                >
                    Book this spot (coming soon)
                </button>
            @else
                <a
                    href="{{ route('login') }}"
                    class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300"
                >
                    Log in to book
                </a>
                <a
                    href="{{ route('register') }}"
                    class="rounded border border-slate-300 px-4 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800"
                >
                    Sign up
                </a>
            @endauth
        </div>
    </article>
</x-layout>