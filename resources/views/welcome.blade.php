<x-layout title="Welcome">
    <section class="rounded border border-slate-200 bg-white p-8 text-center dark:border-slate-800 dark:bg-slate-900">
        <h1 class="text-3xl font-semibold tracking-tight">Find parking without the hassle</h1>
        <p class="mx-auto mt-3 max-w-xl text-sm text-slate-600 dark:text-slate-400">
            ParkEasy helps drivers discover verified parking lots nearby and gives lot owners
            a simple way to list their spots. Browse the available lots and reserve a spot in
            seconds.
        </p>

        <div class="mt-6 flex flex-wrap justify-center gap-3">
            <a
                href="{{ route('parking-lots.browse') }}"
                class="rounded bg-slate-900 px-5 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300"
            >
                Browse parking lots
            </a>
            @guest
                <a
                    href="{{ route('register') }}"
                    class="rounded border border-slate-300 px-5 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800"
                >
                    Create an account
                </a>
            @else
                <a
                    href="{{ route('dashboard') }}"
                    class="rounded border border-slate-300 px-5 py-2 text-sm font-medium hover:bg-slate-100 dark:border-slate-700 dark:hover:bg-slate-800"
                >
                    Go to dashboard
                </a>
            @endguest
        </div>
    </section>

    <section class="mt-8 grid gap-4 sm:grid-cols-3">
        <div class="rounded border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold">For drivers</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Search verified parking lots by name and see live availability.
            </p>
        </div>
        <div class="rounded border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold">For lot owners</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Add your lot, set your hourly rate, and reach more drivers.
            </p>
        </div>
        <div class="rounded border border-slate-200 bg-white p-4 dark:border-slate-800 dark:bg-slate-900">
            <h2 class="text-sm font-semibold">Verified listings</h2>
            <p class="mt-1 text-sm text-slate-600 dark:text-slate-400">
                Every public lot is reviewed by our admin team before going live.
            </p>
        </div>
    </section>
</x-layout>