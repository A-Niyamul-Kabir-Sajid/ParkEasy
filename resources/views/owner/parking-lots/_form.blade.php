@csrf
<div class="space-y-4">
    <div>
        <label for="name" class="mb-1 block text-sm font-medium">Name</label>
        <input id="name" name="name" type="text" value="{{ old('name', $lot->name ?? '') }}" required
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
    </div>

    <div>
        <label for="description" class="mb-1 block text-sm font-medium">Description <span class="text-slate-400">(optional)</span></label>
        <textarea id="description" name="description" rows="3"
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900">{{ old('description', $lot->description ?? '') }}</textarea>
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="latitude" class="mb-1 block text-sm font-medium">Latitude</label>
            <input id="latitude" name="latitude" type="number" step="any" value="{{ old('latitude', $lot->latitude ?? '') }}" required
                class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label for="longitude" class="mb-1 block text-sm font-medium">Longitude</label>
            <input id="longitude" name="longitude" type="number" step="any" value="{{ old('longitude', $lot->longitude ?? '') }}" required
                class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
    </div>

    <div>
        <label for="hourly_rate" class="mb-1 block text-sm font-medium">Hourly rate (BDT)</label>
        <input id="hourly_rate" name="hourly_rate" type="number" step="0.01" min="0" value="{{ old('hourly_rate', $lot->hourly_rate ?? '30.00') }}" required
            class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
    </div>

    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="total_capacity" class="mb-1 block text-sm font-medium">Total capacity</label>
            <input id="total_capacity" name="total_capacity" type="number" min="1" value="{{ old('total_capacity', $lot->total_capacity ?? 50) }}" required
                class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
        <div>
            <label for="available_spots" class="mb-1 block text-sm font-medium">Available spots</label>
            <input id="available_spots" name="available_spots" type="number" min="0" value="{{ old('available_spots', $lot->available_spots ?? 0) }}" required
                class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
        </div>
    </div>
</div>