@extends('layouts.auth', ['title' => 'Verifikasi Google 2FA'])

@section('content')
<div class="text-center mb-6">
    <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-amber-400/10 text-amber-400 border border-amber-400/20 mb-3">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
    </div>
    <h2 class="text-xl font-bold text-white tracking-tight">Otentikasi Dua Langkah (2FA)</h2>
    <p class="text-xs text-slate-400 mt-1">
        Login sebagai: <strong>{{ $user->email }}</strong> ({{ strtoupper($user->role) }})
    </p>
    <p class="text-xs text-slate-400 mt-1">
        Buka Google Authenticator di ponsel Anda dan masukkan 6 angka kode yang tampil.
    </p>
</div>

<form method="POST" action="{{ route('2fa.verify') }}" class="space-y-4">
    @csrf

    <div>
        <label for="one_time_password" class="block text-xs font-semibold uppercase tracking-wider text-slate-300 text-center">
            Kode Verifikasi OTP
        </label>
        <div class="mt-2">
            <input id="one_time_password" name="one_time_password" type="text" inputmode="numeric" maxlength="6" pattern="[0-9]{6}" required autofocus
                placeholder="••••••"
                class="block w-full text-center text-2xl font-mono tracking-[0.5em] rounded-xl border border-slate-700 bg-slate-800/90 px-4 py-2.5 text-white placeholder-slate-600 shadow-sm focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
        </div>
    </div>

    <button type="submit"
        class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold text-slate-950 bg-gradient-to-r from-amber-400 to-amber-500 hover:from-amber-300 hover:to-amber-400 shadow-lg shadow-amber-500/20 transition-all duration-150 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
        Konfirmasi & Buka Dasbor
    </button>
</form>

<div class="mt-6 pt-4 border-t border-slate-800 text-center">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="text-xs text-slate-400 hover:text-rose-400 transition-colors">
            &larr; Batalkan & Kembali ke Halaman Login
        </button>
    </form>
</div>
@endsection
