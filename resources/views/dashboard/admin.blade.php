<x-layout title="Admin dashboard">
    <h1 class="mb-2 text-2xl font-semibold">Admin dashboard</h1>
    <p class="text-sm text-slate-600 dark:text-slate-400">
        Welcome back, {{ auth()->user()->name }}. The verification queue and platform analytics will live here.
    </p>

    <div class="mt-8 rounded border border-dashed border-slate-300 p-8 text-center text-sm text-slate-500 dark:border-slate-700">
        Verification queue lands in Stage 3.
    </div>
</x-layout>