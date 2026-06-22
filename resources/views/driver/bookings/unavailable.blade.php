<x-layout title="No spots available">
    <div class="mb-4">
        <a href="{{ route('parking-lots.show', $parkingLot) }}" class="text-sm text-slate-600 hover:underline dark:text-slate-400">
            &larr; Back to {{ $parkingLot->name }}
        </a>
    </div>

    <div class="rounded border border-amber-300 bg-amber-50 p-6 dark:border-amber-700 dark:bg-amber-950">
        <h1 class="text-lg font-semibold text-amber-900 dark:text-amber-100">No spots available right now</h1>
        <p class="mt-2 text-sm text-amber-800 dark:text-amber-200">
            All {{ (int) $parkingLot->total_capacity }} spots at {{ $parkingLot->name }} are currently occupied.
            Please check back later or pick a different lot.
        </p>
        <div class="mt-4">
            <a href="{{ route('parking-lots.browse') }}" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                Browse other lots
            </a>
        </div>
    </div>
</x-layout>