<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use App\Models\User;

class AuthController extends Controller
{
    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'alpha_dash', 'min:3', 'max:30', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $user = User::create([
            ...$data,
            'password' => Hash::make($data['password']),
            'role' => 'user',
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Akun berhasil dibuat. Selamat datang di Respawn!');
    }

    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function redirectToGoogle(Request $request): RedirectResponse
    {
        $clientId = config('services.google.client_id');
        $redirectUri = config('services.google.redirect');

        if (! $clientId || ! $redirectUri) {
            abort(503, 'Login Google belum dikonfigurasi.');
        }

        $state = Str::random(40);
        $request->session()->put('google_oauth_state', $state);

        $query = http_build_query([
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
            'response_type' => 'code',
            'scope' => 'openid email profile',
            'state' => $state,
        ]);

        return redirect()->away('https://accounts.google.com/o/oauth2/v2/auth?' . $query);
    }

    public function handleGoogleCallback(Request $request): RedirectResponse
    {
        $state = $request->session()->pull('google_oauth_state');
        $callbackState = $request->query('state');

        if (! is_string($state) || ! is_string($callbackState) || ! hash_equals($state, $callbackState)) {
            return redirect()->route('login')->with('error', 'Sesi login Google tidak valid. Silakan coba lagi.');
        }

        if ($request->filled('error')) {
            return redirect()->route('login')->with('error', 'Login Google dibatalkan atau gagal. Silakan coba lagi.');
        }

        $code = $request->query('code');
        $clientId = config('services.google.client_id');
        $clientSecret = config('services.google.client_secret');
        $redirectUri = config('services.google.redirect');

        if (! is_string($code) || ! $clientId || ! $clientSecret || ! $redirectUri) {
            abort(503, 'Login Google belum dikonfigurasi dengan benar.');
        }

        $tokenResponse = Http::asForm()->post('https://oauth2.googleapis.com/token', [
            'code' => $code,
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
            'redirect_uri' => $redirectUri,
            'grant_type' => 'authorization_code',
        ]);

        if (! $tokenResponse->successful() || ! is_string($tokenResponse->json('access_token'))) {
            return redirect()->route('login')->with('error', 'Google tidak dapat memverifikasi login. Silakan coba lagi.');
        }

        $profileResponse = Http::withToken($tokenResponse->json('access_token'))
            ->get('https://openidconnect.googleapis.com/v1/userinfo');

        $profile = $profileResponse->json();
        if (
            ! $profileResponse->successful()
            || ! is_array($profile)
            || ! filter_var($profile['email'] ?? null, FILTER_VALIDATE_EMAIL)
            || ($profile['email_verified'] ?? false) !== true
        ) {
            return redirect()->route('login')->with('error', 'Email Google tidak dapat diverifikasi. Silakan coba akun lain.');
        }

        $email = $profile['email'];
        $user = User::where('email', $email)->first();

        if (! $user) {
            $user = User::create([
                'name' => is_string($profile['name'] ?? null) ? $profile['name'] : $email,
                'username' => $this->uniqueGoogleUsername($email),
                'email' => $email,
                'email_verified_at' => now(),
                'password' => Str::random(40),
                'role' => 'user',
            ]);
        }

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    private function uniqueGoogleUsername(string $email): string
    {
        $base = Str::slug((string) Str::before($email, '@'));
        $base = Str::limit($base ?: 'player', 24, '');
        $username = $base;

        while (User::where('username', $username)->exists()) {
            $username = $base . Str::lower(Str::random(5));
        }

        return $username;
    }

    public function login(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        $field = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
        $authenticated = Auth::attempt([
            $field => $credentials['login'],
            'password' => $credentials['password'],
        ], $request->boolean('remember'));

        if (! $authenticated) {
            throw ValidationException::withMessages([
                'login' => 'Username/email atau password tidak cocok.',
            ]);
        }

        $request->session()->regenerate();

        return redirect()->intended(route('dashboard'));
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }
}
