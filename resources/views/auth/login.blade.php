<x-layout title="Log in">
    <div class="mx-auto max-w-md">
        <h1 class="mb-6 text-2xl font-semibold">Log in</h1>

        <form method="POST" action="{{ route('login') }}" class="space-y-4">
            @csrf

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="remember" value="1" class="rounded">
                Remember me
            </label>

            <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                Log in
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-600 dark:text-slate-400">
            Don't have an account?
            <a href="{{ route('register') }}" class="font-medium underline">Sign up</a>
        </p>
    </div>
</x-layout>