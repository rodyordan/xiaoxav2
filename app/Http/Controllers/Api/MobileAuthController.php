<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules\Password;

class MobileAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->merge([
            'correo' => strtolower(trim((string) $request->correo)),
        ]);

        $request->validate([
            'correo' => 'required|email|max:100',
            'contrasena' => 'required|string|max:72',
        ]);

        $user = Usuario::where('correo', $request->correo)->first();

        if (!$user || !Hash::check($request->contrasena, $user->contrasena)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'El correo electrónico no está verificado.'
            ], 403);
        }

        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id_usuario,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'correo' => $user->correo,
                'id_rol' => $user->id_rol,
                'telefono' => $user->telefono,
                'numero_documento' => $user->numero_documento,
                'id_tipo_documento' => $user->id_tipo_documento,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token
        ]);
    }

    public function register(Request $request)
    {
        $request->merge([
            'nombres' => trim((string) $request->nombres),
            'apellidos' => trim((string) $request->apellidos),
            'correo' => strtolower(trim((string) $request->correo)),
            'telefono' => $request->filled('telefono') ? trim((string) $request->telefono) : null,
            'numero_documento' => $request->filled('numero_documento') ? trim((string) $request->numero_documento) : null,
        ]);

        $request->validate([
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'correo' => 'required|email|max:100|unique:usuario,correo',
            'contrasena' => ['required', 'string', Password::min(8)->letters()->numbers()->max(72)],
            'telefono' => 'nullable|string|max:20',
            'numero_documento' => 'nullable|string|max:20',
            'id_tipo_documento' => 'nullable|integer|exists:tipo_documento,id_tipo_documento',
        ]);

        $user = Usuario::create([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'correo' => $request->correo,
            'contrasena' => Hash::make($request->contrasena),
            'id_rol' => 2,
            'telefono' => $request->telefono,
            'numero_documento' => $request->numero_documento,
            'id_tipo_documento' => $request->id_tipo_documento,
        ]);

        $user->sendEmailVerificationNotification();

        $token = $user->createToken('mobile_token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado correctamente. Por favor, verifica tu correo electrónico.',
            'user' => [
                'id' => $user->id_usuario,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'correo' => $user->correo,
                'id_rol' => $user->id_rol,
                'telefono' => $user->telefono,
                'numero_documento' => $user->numero_documento,
                'id_tipo_documento' => $user->id_tipo_documento,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
            'token' => $token
        ], 201);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada correctamente'
        ]);
    }

    public function perfil(Request $request)
    {
        $user = $request->user();

        return response()->json([
            'id' => $user->id_usuario,
            'nombres' => $user->nombres,
            'apellidos' => $user->apellidos,
            'correo' => $user->correo,
            'id_rol' => $user->id_rol,
            'telefono' => $user->telefono,
            'numero_documento' => $user->numero_documento,
            'id_tipo_documento' => $user->id_tipo_documento,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

    public function updatePerfil(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'nombres' => trim((string) $request->nombres),
            'apellidos' => trim((string) $request->apellidos),
            'telefono' => $request->filled('telefono') ? trim((string) $request->telefono) : null,
        ]);

        $request->validate([
            'nombres' => 'required|string|max:50',
            'apellidos' => 'required|string|max:50',
            'telefono' => 'nullable|string|max:20',
        ]);

        $user->update([
            'nombres' => $request->nombres,
            'apellidos' => $request->apellidos,
            'telefono' => $request->telefono,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Perfil actualizado correctamente',
            'user' => [
                'id' => $user->id_usuario,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'correo' => $user->correo,
                'id_rol' => $user->id_rol,
                'telefono' => $user->telefono,
                'numero_documento' => $user->numero_documento,
                'id_tipo_documento' => $user->id_tipo_documento,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }

    public function updateDatosContacto(Request $request)
    {
        $user = $request->user();

        $request->merge([
            'numero_documento' => trim((string) $request->numero_documento),
            'telefono' => trim((string) $request->telefono),
        ]);

        $request->validate([
            'id_tipo_documento' => 'required|integer|exists:tipo_documento,id_tipo_documento',
            'numero_documento' => 'required|string|max:20',
            'telefono' => 'required|string|max:20',
        ]);

        $user->update([
            'id_tipo_documento' => $request->id_tipo_documento,
            'numero_documento' => $request->numero_documento,
            'telefono' => $request->telefono,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Datos de contacto actualizados correctamente',
            'data' => [
                'id' => $user->id_usuario,
                'nombres' => $user->nombres,
                'apellidos' => $user->apellidos,
                'correo' => $user->correo,
                'id_rol' => $user->id_rol,
                'telefono' => $user->telefono,
                'numero_documento' => $user->numero_documento,
                'id_tipo_documento' => $user->id_tipo_documento,
                'created_at' => $user->created_at,
                'updated_at' => $user->updated_at,
            ],
        ]);
    }
}