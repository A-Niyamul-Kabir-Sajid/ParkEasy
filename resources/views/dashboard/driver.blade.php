<x-layout title="Driver dashboard">
    <h1 class="mb-2 text-2xl font-semibold">Driver dashboard</h1>
    <p class="text-sm text-slate-600 dark:text-slate-400">
        Welcome back, {{ auth()->user()->name }}. Find and book verified parking near you.
    </p>

    @php
        $upcomingCount = auth()->user()->bookings()
            ->where('status', 'active')
            ->where('start_time', '>=', now())
            ->count();
    @endphp

    <div class="mt-8 grid gap-4 sm:grid-cols-2">
        <a href="{{ route('parking-lots.browse') }}"
            class="rounded border border-slate-200 bg-white p-5 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700">
            <h2 class="text-base font-semibold">Browse parking</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">Find a verified spot near you.</p>
        </a>
        <a href="{{ route('driver.bookings.index') }}"
            class="rounded border border-slate-200 bg-white p-5 hover:border-slate-300 dark:border-slate-800 dark:bg-slate-900 dark:hover:border-slate-700">
            <h2 class="text-base font-semibold">My bookings</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                {{ $upcomingCount }} upcoming {{ \Illuminate\Support\Str::plural('booking', $upcomingCount) }}.
            </p>
        </a>
    </div>
</x-layout>