<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\Usuario;

class AuthController extends Controller
{
    /**
     * Registro de un nuevo usuario
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'nombre'   => 'required|string|max:150',
            'email'    => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol'      => 'required|in:admin,usuario',
        ]);

        $validated['password'] = Hash::make($validated['password']);
        $usuario = Usuario::create($validated);

        // Generar token
        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Usuario registrado exitosamente',
            'usuario' => $usuario,
            'token'   => $token,
        ], 201);
    }

    /**
     * Login de usuario existente
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ]);

        $usuario = Usuario::where('email', $request->email)->first();

        if (! $usuario || ! Hash::check($request->password, $usuario->password)) {
            return response()->json([
                'message' => 'Credenciales inválidas',
                'error' => 'Unauthorized',
                'status' => false
            ], 401);
        }

        // Generar token
        $token = $usuario->createToken('api-token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'usuario' => $usuario,
            'token'   => $token,
        ]);
    }

    /**
     * Logout (revocar tokens)
     */
    public function logout(Request $request)
    {
        try {
            // Revocar solo el token actual del usuario
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'message' => 'Logout exitoso, sesión cerrada correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en logout: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cerrar sesión'
            ], 500);
        }
    }
}
