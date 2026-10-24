<?php

namespace App\Http\Requests\Auth;

use App\Models\Usuario;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'correo'     => ['required', 'string', 'email'],
            'contrasena' => ['required', 'string'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $usuario = Usuario::where('correo', $this->correo)->first();

        if (! $usuario) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'correo' => 'El correo electrónico es incorrecto.',
            ]);
        }

        if (! Hash::check($this->contrasena, $usuario->contrasena)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'contrasena' => 'La contraseña es incorrecta.',
            ]);
        }

        if (! $usuario->hasVerifiedEmail()) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'correo' => 'El correo electrónico no está verificado.',
            ]);
        }

        Auth::login($usuario, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'correo' => "Demasiados intentos. Inténtalo de nuevo en {$seconds} segundos.",
        ]);
    }

    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('correo')).'|'.$this->ip());
    }
}