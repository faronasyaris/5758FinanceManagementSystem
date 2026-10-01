<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Display a listing of system users with their 2FA status (Owner only).
     */
    public function index(): View
    {
        $users = User::orderBy('id', 'asc')->get();

        return view('users.index', [
            'users' => $users,
        ]);
    }

    /**
     * Reset Google Authenticator 2FA secret for a specific user.
     */
    public function resetTwoFactor(Request $request, User $user): RedirectResponse
    {
        $user->google2fa_secret = null;
        $user->save();

        return redirect()->route('users.index')->with('success', "Google Authenticator untuk {$user->name} ({$user->email}) berhasil di-reset. Pengguna akan diminta scan QR Code baru saat login berikutnya.");
    }
}
