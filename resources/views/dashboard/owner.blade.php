<x-layout title="Owner dashboard">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Owner dashboard</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Welcome back, {{ auth()->user()->name }}. Manage your parking lots and reservations here.
            </p>
        </div>
        <a href="{{ route('owner.parking-lots.create') }}"
            class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
            + New parking lot
        </a>
    </div>

    <div class="rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">
        Your parking lots and bookings will appear here once Stage 2 lands.
    </div>
</x-layout>