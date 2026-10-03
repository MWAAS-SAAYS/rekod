<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Portal Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-900 border border-slate-800 rounded-xl p-8 shadow-2xl">
        <div class="mb-8 text-center">
            <h1 class="text-2xl font-bold text-white">Industry Portal</h1>
            <p class="text-slate-400 text-sm mt-1">Sign in to manage industrial attachments and evaluations</p>
        </div>

        @if (session('status'))
            <div class="mb-4 p-3 bg-blue-500/10 border border-blue-500/30 text-blue-400 text-xs rounded-lg">
                {{ session('status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mb-4 p-3 bg-rose-500/10 border border-rose-500/30 text-rose-400 text-xs rounded-lg">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="space-y-5">
            @csrf
            <div>
                <label for="email" class="block text-xs font-semibold uppercase text-slate-400 mb-2">Work Email</label>
                <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div>
                <label for="password" class="block text-xs font-semibold uppercase text-slate-400 mb-2">Password</label>
                <input id="password" type="password" name="password" required
                       class="w-full px-4 py-2.5 bg-slate-800 border border-slate-700 rounded-lg text-white text-sm focus:outline-none focus:border-indigo-500 transition">
            </div>

            <div class="flex items-center justify-between text-xs text-slate-400">
                <label class="flex items-center">
                    <input type="checkbox" name="remember" class="rounded bg-slate-800 border-slate-700 text-indigo-600 focus:ring-0">
                    <span class="ml-2">Remember device</span>
                </label>
                @if (Route::has('password.request'))
                    <a href="{{ route('password.request') }}" class="hover:text-indigo-400 transition">Forgot password?</a>
                @endif
            </div>

            <button type="submit" class="w-full py-2.5 px-4 bg-indigo-600 hover:bg-indigo-500 text-white rounded-lg text-sm font-semibold transition">
                Authenticate Account
            </button>
        </form>

        <div class="mt-8 pt-6 border-t border-slate-800 text-center text-xs text-slate-400">
            Need to register your organization?
            <a href="{{ route('company.register') }}" class="text-indigo-400 hover:underline font-medium">Apply for access</a>
        </div>
    </div>
</body>
</html>