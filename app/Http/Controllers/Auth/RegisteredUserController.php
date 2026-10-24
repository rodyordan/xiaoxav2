<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'nombres' => trim((string) $request->nombres),
            'apellidos' => trim((string) $request->apellidos),
            'correo' => strtolower(trim((string) $request->correo)),
        ]);

        $request->validate([
            'nombres'   => ['required', 'string', 'max:50'],
            'apellidos' => ['required', 'string', 'max:50'],
            'correo'    => ['required', 'string', 'lowercase', 'email', 'max:100', 'unique:usuario,correo'],
            'password'  => ['required', 'confirmed', Rules\Password::min(8)->letters()->numbers()->max(72)],
        ], [
            'nombres.required'    => 'Los nombres son obligatorios.',
            'nombres.string'      => 'Los nombres deben ser texto válido.',
            'nombres.max'         => 'Los nombres no pueden tener más de 50 caracteres.',

            'apellidos.required'  => 'Los apellidos son obligatorios.',
            'apellidos.string'    => 'Los apellidos deben ser texto válido.',
            'apellidos.max'       => 'Los apellidos no pueden tener más de 50 caracteres.',

            'correo.required'     => 'El correo electrónico es obligatorio.',
            'correo.email'        => 'Ingresa un correo electrónico válido.',
            'correo.unique'       => 'Este correo ya está registrado.',
            'correo.max'          => 'El correo no puede tener más de 100 caracteres.',

            'password.required'   => 'La contraseña es obligatoria.',
            'password.confirmed'  => 'Las contraseñas no coinciden.',
            'password.min'        => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max'        => 'La contraseña no puede tener más de 72 caracteres.',
            'password.letters'    => 'La contraseña debe incluir al menos una letra.',
            'password.numbers'    => 'La contraseña debe incluir al menos un número.',
        ]);

        $user = Usuario::create([
            'nombres'    => $request->nombres,
            'apellidos'  => $request->apellidos,
            'correo'     => $request->correo,
            'contrasena' => Hash::make($request->password),
            'id_rol'     => 2,
        ]);

        $user->sendEmailVerificationNotification();

        return redirect()->route('verification.notice');
    }
}