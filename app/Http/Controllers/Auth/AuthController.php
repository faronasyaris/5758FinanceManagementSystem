<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use PragmaRX\Google2FA\Google2FA;

class AuthController extends Controller
{
    /**
     * Show login form (strictly no public registration).
     */
    public function showLoginForm(): View|RedirectResponse
    {
        if (Auth::check() && session('auth.2fa.authenticated')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Handle email & password authentication.
     */
    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return back()->withInput($request->only('email'))->withErrors([
                'email' => 'Email atau kata sandi yang Anda masukkan salah.',
            ]);
        }

        // Store user ID in session temporarily before 2FA check
        session([
            'auth.2fa.user_id' => $user->id,
            'auth.2fa.remember' => $request->boolean('remember'),
        ]);

        // First login: if google2fa_secret is null, strictly enforce 2FA Setup
        if (empty($user->google2fa_secret)) {
            return redirect()->route('2fa.setup');
        }

        // Subsequent logins: verify OTP
        return redirect()->route('2fa.verify');
    }

    /**
     * Show 2FA First-Login Setup Page with QR Code.
     */
    public function showTwoFactorSetup(): View|RedirectResponse
    {
        $userId = session('auth.2fa.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        // If user already has 2FA configured, redirect to verify
        if (!empty($user->google2fa_secret)) {
            return redirect()->route('2fa.verify');
        }

        $google2fa = new Google2FA();
        $secret = session('auth.2fa.secret') ?? $google2fa->generateSecretKey();
        session(['auth.2fa.secret' => $secret]);

        $companyName = '5758inc Finance';
        $qrCodeUrl = $google2fa->getQRCodeUrl($companyName, $user->email, $secret);

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.two-factor-setup', [
            'user' => $user,
            'secret' => $secret,
            'qrCodeSvg' => $qrCodeSvg,
        ]);
    }

    /**
     * Verify initial OTP and save Google2FA secret for user.
     */
    public function twoFactorSetup(Request $request): RedirectResponse
    {
        $request->validate([
            'one_time_password' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('auth.2fa.user_id');
        $secret = session('auth.2fa.secret');

        if (!$userId || !$secret) {
            return redirect()->route('login')->withErrors([
                'email' => 'Sesi setup 2FA telah kedaluwarsa. Silakan login kembali.',
            ]);
        }

        $user = User::findOrFail($userId);
        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($secret, $request->input('one_time_password'), 2);

        if (!$isValid) {
            return back()->withErrors([
                'one_time_password' => 'Kode OTP salah atau telah kedaluwarsa. Masukkan 6 angka dari Google Authenticator.',
            ]);
        }

        // Save 2FA secret
        $user->google2fa_secret = $secret;
        $user->save();

        // Complete login
        $remember = session('auth.2fa.remember', false);
        Auth::login($user, $remember);

        session()->forget(['auth.2fa.user_id', 'auth.2fa.secret', 'auth.2fa.remember']);
        session(['auth.2fa.authenticated' => true]);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Autentikasi Dua Langkah (2FA) berhasil disiapkan. Selamat datang!');
    }

    /**
     * Show OTP Verification Page for subsequent logins.
     */
    public function showTwoFactorVerify(): View|RedirectResponse
    {
        $userId = session('auth.2fa.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);

        if (empty($user->google2fa_secret)) {
            return redirect()->route('2fa.setup');
        }

        return view('auth.two-factor-verify', [
            'user' => $user,
        ]);
    }

    /**
     * Verify OTP for subsequent logins.
     */
    public function twoFactorVerify(Request $request): RedirectResponse
    {
        $request->validate([
            'one_time_password' => ['required', 'string', 'size:6'],
        ]);

        $userId = session('auth.2fa.user_id');
        if (!$userId) {
            return redirect()->route('login');
        }

        $user = User::findOrFail($userId);
        $google2fa = new Google2FA();
        $isValid = $google2fa->verifyKey($user->google2fa_secret, $request->input('one_time_password'), 2);

        if (!$isValid) {
            return back()->withErrors([
                'one_time_password' => 'Kode OTP tidak sesuai. Pastikan jam ponsel Anda sinkron dan masukkan 6 angka terbaru.',
            ]);
        }

        $remember = session('auth.2fa.remember', false);
        Auth::login($user, $remember);

        session()->forget(['auth.2fa.user_id', 'auth.2fa.remember']);
        session(['auth.2fa.authenticated' => true]);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    /**
     * Logout user and clear session.
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('status', 'Anda telah berhasil keluar dari sistem.');
    }
}
