<x-layout title="Leave a review">
    <div class="mb-4">
        <a href="{{ route('parking-lots.show', $parkingLot) }}" class="text-sm text-slate-600 hover:underline dark:text-slate-400">
            &larr; Back to {{ $parkingLot->name }}
        </a>
    </div>

    <article class="mx-auto max-w-xl rounded border border-slate-200 bg-white p-6 dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-xl font-semibold">Leave a review</h1>
        <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
            You completed a booking at <span class="font-medium">{{ $parkingLot->name }}</span>. Tell other drivers what you thought.
        </p>

        @if ($errors->any())
            <div class="mt-4 rounded border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-800 dark:border-rose-900 dark:bg-rose-950 dark:text-rose-200">
                <ul class="list-disc space-y-1 pl-5">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('driver.reviews.store', $parkingLot) }}" class="mt-6 space-y-5">
            @csrf
            <input type="hidden" name="parking_lot_id" value="{{ $parkingLot->id }}">

            <fieldset>
                <legend class="text-sm font-medium">Rating</legend>
                <div class="mt-2 flex items-center gap-3" x-data="{ rating: {{ old('rating', 5) }} }">
                    @for ($i = 1; $i <= 5; $i++)
                        <label class="cursor-pointer text-2xl">
                            <input
                                type="radio"
                                name="rating"
                                value="{{ $i }}"
                                class="sr-only"
                                @checked(old('rating', 5) == $i)
                                x-on:click="rating = {{ $i }}"
                            >
                            <span x-bind:class="rating >= {{ $i }} ? 'text-amber-500' : 'text-slate-300 dark:text-slate-600'">&#9733;</span>
                        </label>
                    @endfor
                </div>
            </fieldset>

            <div>
                <label for="comment" class="block text-sm font-medium">Comment <span class="text-slate-500 dark:text-slate-400">(optional)</span></label>
                <textarea
                    id="comment"
                    name="comment"
                    rows="4"
                    maxlength="1000"
                    class="mt-1 w-full rounded border border-slate-300 bg-white px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-950"
                    placeholder="What did you like or dislike?"
                >{{ old('comment') }}</textarea>
            </div>

            <div class="flex items-center justify-end gap-3">
                <a href="{{ route('parking-lots.show', $parkingLot) }}" class="text-sm underline">Cancel</a>
                <button type="submit" class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                    Submit review
                </button>
            </div>
        </form>
    </article>
</x-layout>