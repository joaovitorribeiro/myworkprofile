<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sobrenome' => 'required|string|max:255',
            'username' => 'required|string|min:3|max:255|unique:users,username|regex:/^[a-zA-Z0-9_]+$/',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'data_nascimento' => 'required|date|before:today|after:1900-01-01',
            'country_id' => 'required|integer|exists:countries,id',
            'state_id' => 'required|integer|exists:states,id',
            'city_id' => 'required|integer|exists:cities,id',
            // Aceite de termos deve vir marcado no front, mas garantimos no backend
            'accept_terms' => 'accepted',
        ], [
            'accept_terms.accepted' => 'Você deve aceitar os Termos para criar a conta.',
        ]);

        // Calcular a idade a partir da data de nascimento
        $dataNascimento = new \DateTime($request->data_nascimento);
        $hoje = new \DateTime();
        $idade = $hoje->diff($dataNascimento)->y;

        // Validar se a idade calculada está dentro do range permitido
        if ($idade < 13 || $idade > 120) {
            return back()->withErrors([
                'data_nascimento' => 'A idade deve estar entre 13 e 120 anos.'
            ]);
        }

        $user = User::create([
            'name' => $request->name,
            'sobrenome' => $request->sobrenome,
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'data_nascimento' => $request->data_nascimento,
            'idade' => $idade,
            'country_id' => $request->country_id,
            'state_id' => $request->state_id,
            'city_id' => $request->city_id,
        ]);

        // Registrar consentimentos legais no momento do cadastro
        try {
            $ip = $request->ip();
            $ua = $request->userAgent();
            $source = 'register_form';
            $versionTerms = config('legal.terms_version', '1.0');
            $versionPrivacy = config('legal.privacy_version', '1.0');

            \App\Models\UserConsent::create([
                'user_id' => $user->id,
                'type' => 'terms',
                'version' => $versionTerms,
                'accepted' => true,
                'ip' => $ip,
                'user_agent' => $ua,
                'source' => $source,
                'consented_at' => now(),
            ]);

            \App\Models\UserConsent::create([
                'user_id' => $user->id,
                'type' => 'privacy',
                'version' => $versionPrivacy,
                'accepted' => true,
                'ip' => $ip,
                'user_agent' => $ua,
                'source' => $source,
                'consented_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('Falha ao registrar consentimentos no cadastro: '.$e->getMessage());
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('validation', absolute: false));
    }
}
