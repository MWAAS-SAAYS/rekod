@extends('layouts.my_app')

@section('title', 'Student Registration')

@section('content')
<div class="min-h-[85vh] flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8 bg-slate-50/50">
    <div class="max-w-md w-full space-y-6 bg-white p-8 rounded-2xl border border-slate-200/80 shadow-xl relative overflow-hidden">
        <div class="absolute top-0 left-0 right-0 h-2 bg-gradient-to-r from-amber-500 via-amber-400 to-amber-600"></div>
        
        <div class="text-center space-y-2">
            <div class="w-12 h-12 rounded-2xl bg-[#0b1329] text-amber-400 border border-amber-500/20 flex items-center justify-center mx-auto text-xl shadow-md">
                <i class="fas fa-user-plus"></i>
            </div>
            <h2 class="text-xl font-black text-slate-900 uppercase tracking-wider">Create Student Account</h2>
            <p class="text-xs text-slate-500">Register to access placement tracking and logbook submissions</p>
        </div>

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf

            <div class="space-y-1.5">
                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Full Name</label>
                <div class="relative">
                    <i class="fas fa-user absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="name" name="name" required placeholder="John Doe"
                           class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="reg_number" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Registration Number</label>
                <div class="relative">
                    <i class="fas fa-id-card absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="text" id="reg_number" name="reg_number" required placeholder="E024-01-1234/2026"
                           class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">University Email</label>
                <div class="relative">
                    <i class="fas fa-envelope absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="email" id="email" name="email" required placeholder="student@dkut.ac.ke"
                           class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                </div>
            </div>

            <div class="space-y-1.5">
                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider">Password</label>
                <div class="relative">
                    <i class="fas fa-lock absolute left-3.5 top-1/2 -translate-y-1/2 text-slate-400 text-xs"></i>
                    <input type="password" id="password" name="password" required placeholder="••••••••"
                           class="w-full pl-9 pr-4 py-2.5 text-xs rounded-xl border border-slate-200 text-slate-800 bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/20 focus:border-amber-500 transition-all">
                </div>
            </div>

            <button type="submit" class="w-full py-3 px-4 rounded-xl bg-amber-500 hover:bg-amber-400 text-slate-950 font-black text-xs transition-all shadow-md shadow-amber-500/10 uppercase tracking-wider mt-2">
                Complete Registration
            </button>
        </form>
    </div>
</div>
@endsection