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

        @php
            $avg = $lot->averageRating();
            $count = $lot->reviewCount();
        @endphp

        @if ($avg !== null)
            <div class="mt-3 flex items-center gap-2 text-sm">
                <span class="text-amber-500" aria-hidden="true">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                <span class="font-medium">{{ number_format($avg, 1) }}</span>
                <span class="text-slate-600 dark:text-slate-400">
                    ({{ $count }} {{ \Illuminate\Support\Str::plural('review', $count) }})
                </span>
            </div>
        @endif

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
                @if (auth()->user()->isDriver())
                    @if ((int) $lot->available_spots > 0)
                        <a
                            href="{{ route('driver.bookings.create', $lot) }}"
                            class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300"
                        >
                            Book this spot
                        </a>
                    @else
                        <span class="rounded bg-slate-200 px-4 py-2 text-sm font-medium text-slate-600 dark:bg-slate-800 dark:text-slate-300">
                            No spots available
                        </span>
                    @endif
                    <a href="{{ route('driver.bookings.index') }}" class="text-sm underline">
                        My bookings
                    </a>
                @elseif (auth()->user()->isOwner())
                    <a href="{{ route('owner.parking-lots.bookings', $lot) }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                        View bookings
                    </a>
                @else
                    <span class="text-sm text-slate-500 dark:text-slate-400">Bookings are only available to drivers.</span>
                @endif
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

    @php
        $recentReviews = $lot->reviews()->with('driver')->latest()->limit(5)->get();
    @endphp

    @if ($recentReviews->isNotEmpty())
        <section class="mt-8">
            <h2 class="text-lg font-semibold">Recent reviews</h2>
            <ul class="mt-3 space-y-3">
                @foreach ($recentReviews as $review)
                    <li class="rounded border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
                        <div class="flex items-center justify-between text-sm">
                            <span class="font-medium">{{ $review->driver?->name ?? 'Anonymous' }}</span>
                            <span class="text-amber-500" aria-label="Rating">
                                @for ($i = 1; $i <= 5; $i++)
                                    <span @class(['text-slate-300 dark:text-slate-600' => $i > $review->rating])>&#9733;</span>
                                @endfor
                            </span>
                        </div>
                        @if ($review->comment)
                            <p class="mt-2 text-sm text-slate-700 dark:text-slate-300">{{ $review->comment }}</p>
                        @endif
                    </li>
                @endforeach
            </ul>
        </section>
    @endif
</x-layout>