<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Corporate Identity Verification Pending</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-900 text-slate-100 min-h-screen flex items-center justify-center p-6">
    <div class="max-w-md w-full bg-slate-800 border border-slate-700 rounded-xl p-8 shadow-2xl text-center">
        <div class="mx-auto w-16 h-16 bg-amber-500/10 text-amber-500 rounded-full flex items-center justify-center mb-6">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold mb-2">Verification Under Review</h1>
        <p class="text-slate-400 text-sm mb-6">
            Your corporate account and registration credentials have been submitted. Our administrative team is currently vetting your organization details.
        </p>

        <div class="bg-slate-900/60 rounded-lg p-4 mb-6 text-left border border-slate-700/50">
            <div class="text-xs font-semibold uppercase text-slate-400 mb-1">Status</div>
            <div class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-amber-500/20 text-amber-400 border border-amber-500/30">
                Pending Approval
            </div>
        </div>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="w-full py-2.5 px-4 bg-slate-700 hover:bg-slate-600 text-white rounded-lg text-sm font-medium transition">
                Sign Out
            </button>
        </form>
    </div>
</body>
</html>