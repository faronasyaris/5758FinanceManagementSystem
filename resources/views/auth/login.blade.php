@extends('layouts.auth', ['title' => 'Masuk Sistem'])

@section('content')
<div class="mb-6">
    <h2 class="text-xl font-bold text-white tracking-tight">Masuk Sistem</h2>
    <p class="text-xs text-slate-400 mt-1">Gunakan akun resmi yang terdaftar untuk mengakses data finansial.</p>
</div>

<form method="POST" action="{{ route('login') }}" class="space-y-5">
    @csrf

    <div>
        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Alamat Email</label>
        <div class="mt-1.5">
            <input id="email" name="email" type="email" autocomplete="email" required value="{{ old('email') }}"
                placeholder="nama@5758inc.com"
                class="block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>
    </div>

    <div>
        <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300">Kata Sandi</label>
        <div class="mt-1.5">
            <input id="password" name="password" type="password" autocomplete="current-password" required
                placeholder="••••••••"
                class="block w-full rounded-xl border border-slate-700 bg-slate-800/80 px-4 py-2.5 text-sm text-white placeholder-slate-500 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/20">
        </div>
    </div>

    <div class="flex items-center justify-between">
        <label class="flex items-center text-xs text-slate-400 cursor-pointer">
            <input type="checkbox" name="remember" class="w-4 h-4 rounded border-slate-700 bg-slate-800 text-amber-500 focus:ring-amber-400 focus:ring-offset-slate-900">
            <span class="ml-2">Ingat sesi saya</span>
        </label>
        <span class="text-xs text-slate-500 italic">2FA Enforced</span>
    </div>

    <button type="submit"
        class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-lg shadow-amber-500/20 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
        Lanjutkan Verifikasi
        <svg class="w-4 h-4 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
    </button>
</form>

<div class="mt-6 pt-5 border-t border-slate-800/80 text-center">
    <p class="text-xs text-slate-500">
        Sistem tertutup. Tidak menyediakan pendaftaran publik.
    </p>
</div>
@endsection
