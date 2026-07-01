<x-layout title="Browse parking lots">
    <div class="mb-6 flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Browse parking lots</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Find a verified spot near you.
            </p>
        </div>

        <form method="GET" action="{{ route('parking-lots.browse') }}" class="flex w-full gap-2 sm:w-auto">
            <input
                type="search"
                name="q"
                value="{{ $search }}"
                placeholder="Search by name"
                class="w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900 sm:w-64"
            >
            <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                Search
            </button>
        </form>
    </div>

    @if ($lots->isEmpty())
        <div class="rounded border border-dashed border-slate-300 bg-white px-4 py-12 text-center text-sm text-slate-600 dark:border-slate-700 dark:bg-slate-900 dark:text-slate-400">
            @if ($search !== '')
                No verified parking lots match &ldquo;{{ $search }}&rdquo;.
            @else
                No verified parking lots yet. Check back soon.
            @endif
        </div>
    @else
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($lots as $lot)
                <a
                    href="{{ route('parking-lots.show', $lot) }}"
                    class="block rounded border border-slate-200 bg-white p-4 transition hover:border-slate-400 hover:shadow-sm dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-600"
                >
                    <div class="flex items-start justify-between gap-2">
                        <h2 class="text-base font-semibold">{{ $lot->name }}</h2>
                        <span class="shrink-0 rounded bg-emerald-100 px-2 py-0.5 text-xs font-medium text-emerald-800 dark:bg-emerald-950 dark:text-emerald-200">
                            Verified
                        </span>
                    </div>

                    @if ($lot->description)
                        <p class="mt-1 line-clamp-2 text-sm text-slate-600 dark:text-slate-400">
                            {{ $lot->description }}
                        </p>
                    @endif

                    <dl class="mt-3 grid grid-cols-2 gap-2 text-sm">
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Hourly rate</dt>
                            <dd class="font-medium">৳{{ number_format((float) $lot->hourly_rate, 2) }}</dd>
                        </div>
                        <div>
                            <dt class="text-slate-500 dark:text-slate-400">Capacity</dt>
                            <dd class="font-medium">{{ (int) $lot->available_spots }} / {{ (int) $lot->total_capacity }}</dd>
                        </div>
                    </dl>

                    @if ($lot->owner)
                        <p class="mt-3 text-xs text-slate-500 dark:text-slate-400">
                            Managed by {{ $lot->owner->name }}
                        </p>
                    @endif
                </a>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $lots->links() }}
        </div>
    @endif
</x-layout>