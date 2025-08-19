<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Exemplo de uso do Doctrine DBAL para buscar um usuário
        if ($request->username) {
            $userFromDbal = \App\Models\User::findByUsernameWithDbal($request->username);

            if ($userFromDbal) {
                // Faça algo com o usuário encontrado via DBAL, se necessário
                // Por exemplo, logar o ID do usuário para depuração
                \Illuminate\Support\Facades\Log::info('Usuário encontrado via DBAL: ' . $userFromDbal->id);
            }
        }

        return redirect()->intended(route('dashboard', absolute: false));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        $request->session()->flush();

        return redirect('/login')->with('message', 'Logout realizado com sucesso.');
    }
}
