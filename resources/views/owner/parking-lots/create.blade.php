<x-layout title="New parking lot">
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-6 text-2xl font-semibold">List a new parking lot</h1>
        <p class="mb-6 text-sm text-slate-600 dark:text-slate-400">
            Your lot will be marked as <strong>pending</strong> until an admin verifies it.
        </p>

        <form method="POST" action="{{ route('owner.parking-lots.store') }}" class="space-y-6">
            @method('POST')
            @include('owner.parking-lots._form')

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('owner.parking-lots.index') }}" class="text-sm underline">Cancel</a>
                <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                    Submit for verification
                </button>
            </div>
        </form>
    </div>
</x-layout>