<x-layout title="Sign up">
    <div class="mx-auto max-w-md">
        <h1 class="mb-6 text-2xl font-semibold">Create your account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-4">
            @csrf

            <div>
                <label for="name" class="mb-1 block text-sm font-medium">Full name</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autofocus
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <div>
                <label for="email" class="mb-1 block text-sm font-medium">Email</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <div>
                <label for="phone" class="mb-1 block text-sm font-medium">Phone <span class="text-slate-400">(optional)</span></label>
                <input id="phone" name="phone" type="tel" value="{{ old('phone') }}"
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium">I want to</label>
                <div class="grid grid-cols-2 gap-2">
                    @foreach ($roles as $roleValue)
                        <label class="flex cursor-pointer flex-col gap-0.5 rounded border border-slate-300 px-3 py-2 text-sm has-[:checked]:border-slate-900 has-[:checked]:bg-slate-100 dark:border-slate-700 dark:has-[:checked]:border-slate-100 dark:has-[:checked]:bg-slate-800">
                            <span class="flex items-center gap-2">
                                <input type="radio" name="role" value="{{ $roleValue }}" {{ old('role', 'driver') === $roleValue ? 'checked' : '' }} required>
                                <span class="font-medium capitalize">{{ $roleValue }}</span>
                            </span>
                            <span class="text-xs text-slate-500 dark:text-slate-400">
                                {{ $roleValue === 'owner' ? 'List my parking lot' : 'Find parking' }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div>
                <label for="password" class="mb-1 block text-sm font-medium">Password</label>
                <input id="password" name="password" type="password" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <div>
                <label for="password_confirmation" class="mb-1 block text-sm font-medium">Confirm password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required
                    class="w-full rounded border border-slate-300 px-3 py-2 text-sm focus:border-slate-500 focus:outline-none dark:border-slate-700 dark:bg-slate-900" />
            </div>

            <button type="submit" class="w-full rounded bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-700 dark:bg-slate-100 dark:text-slate-900 dark:hover:bg-slate-300">
                Create account
            </button>
        </form>

        <p class="mt-4 text-center text-sm text-slate-600 dark:text-slate-400">
            Already have an account?
            <a href="{{ route('login') }}" class="font-medium underline">Log in</a>
        </p>
    </div>
</x-layout>