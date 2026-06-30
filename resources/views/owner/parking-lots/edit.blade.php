<x-layout title="Edit parking lot">
    <div class="mx-auto max-w-2xl">
        <h1 class="mb-6 text-2xl font-semibold">Edit parking lot</h1>

        <form method="POST" action="{{ route('owner.parking-lots.update', $lot) }}" class="space-y-6">
            @method('PUT')
            @include('owner.parking-lots._form')

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('owner.parking-lots.index') }}" class="text-sm underline">Cancel</a>
                <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                    Save changes
                </button>
            </div>
        </form>
    </div>
</x-layout>