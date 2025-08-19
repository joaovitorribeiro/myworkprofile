<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Aceita email_or_username (novo) ou email (legado - testes/compatibilidade)
            'email_or_username' => ['required_without:email', 'string'],
            'email' => ['sometimes', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $identifierRaw = $this->input('email_or_username') ?: $this->input('email');
        $password = $this->input('password');

        $identifier = is_string($identifierRaw) ? trim($identifierRaw) : $identifierRaw;
        $isEmail = filter_var($identifier, FILTER_VALIDATE_EMAIL) !== false;

        // Normaliza username: remove '#' inicial se presente e não for e-mail
        if (!$isEmail && is_string($identifier)) {
            $identifier = Str::startsWith($identifier, '#') ? substr($identifier, 1) : $identifier;
        }

        // Validação baseada no banco
        $exists = $isEmail
            ? User::where('email', $identifier)->exists()
            : User::where('username', $identifier)->exists();

        if (!$exists) {
            RateLimiter::hit($this->throttleKey());
            throw ValidationException::withMessages([
                'email_or_username' => 'Usuário não encontrado.',
            ]);
        }

        $credentials = $isEmail
            ? ['email' => $identifier, 'password' => $password]
            : ['username' => $identifier, 'password' => $password];

        if (!Auth::attempt($credentials, $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'password' => trans('auth.password'),
            ]);
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (!RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email_or_username' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        $identifier = (string) ($this->string('email_or_username') ?: $this->string('email'));
        $identifier = trim($identifier);
        // Se não for e-mail, remove '#' inicial para unificar a chave
        if (strpos($identifier, '@') === false) {
            $identifier = ltrim($identifier, '#');
        }
        return Str::transliterate(Str::lower($identifier) . '|' . $this->ip());
    }
}
