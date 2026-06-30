<x-layout title="Admin dashboard">
    @php
        $pendingCount = App\Models\ParkingLot::query()
            ->where('verification_status', App\Enums\ParkingLotVerificationStatus::Pending->value)
            ->count();
        $verifiedCount = App\Models\ParkingLot::query()
            ->where('verification_status', App\Enums\ParkingLotVerificationStatus::Verified->value)
            ->count();
        $rejectedCount = App\Models\ParkingLot::query()
            ->where('verification_status', App\Enums\ParkingLotVerificationStatus::Rejected->value)
            ->count();
    @endphp

    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-semibold">Admin dashboard</h1>
            <p class="text-sm text-slate-600 dark:text-slate-400">
                Welcome back, {{ auth()->user()->name }}. Review submitted parking lots and monitor platform activity.
            </p>
        </div>
        <a href="{{ route('admin.verification.index') }}"
            class="rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
            Open verification queue
        </a>
    </div>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
        <div class="rounded border border-amber-300 bg-amber-50 p-4 dark:border-amber-700 dark:bg-amber-950">
            <div class="text-xs uppercase tracking-wide text-amber-700 dark:text-amber-300">Pending</div>
            <div class="mt-1 text-2xl font-semibold text-amber-900 dark:text-amber-100">{{ $pendingCount }}</div>
        </div>
        <div class="rounded border border-emerald-300 bg-emerald-50 p-4 dark:border-emerald-700 dark:bg-emerald-950">
            <div class="text-xs uppercase tracking-wide text-emerald-700 dark:text-emerald-300">Verified</div>
            <div class="mt-1 text-2xl font-semibold text-emerald-900 dark:text-emerald-100">{{ $verifiedCount }}</div>
        </div>
        <div class="rounded border border-rose-300 bg-rose-50 p-4 dark:border-rose-700 dark:bg-rose-950">
            <div class="text-xs uppercase tracking-wide text-rose-700 dark:text-rose-300">Rejected</div>
            <div class="mt-1 text-2xl font-semibold text-rose-900 dark:text-rose-100">{{ $rejectedCount }}</div>
        </div>
    </div>
</x-layout>