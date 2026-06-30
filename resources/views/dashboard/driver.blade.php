<x-layout title="Driver dashboard">
    <h1 class="mb-2 text-2xl font-semibold">Driver dashboard</h1>
    <p class="text-sm text-slate-600 dark:text-slate-400">
        Welcome back, {{ auth()->user()->name }}. Find and book verified parking near you.
    </p>

    <div class="mt-8 rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">
        Search and bookings land in Stages 4–5.
    </div>
</x-layout>