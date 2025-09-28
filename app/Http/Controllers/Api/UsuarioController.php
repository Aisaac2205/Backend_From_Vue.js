<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    /**
     * Validar que el usuario esté autenticado
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|null
     */
    private function validateAuthentication(Request $request)
    {
        if (!$request->user()) {
            return response()->json([
                'message' => 'Token de acceso requerido o inválido',
                'error' => 'Unauthorized',
                'status' => false
            ], 401);
        }
        return null;
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        try {
            $usuarios = Usuario::all();
            \Log::info('Usuarios encontrados: ' . $usuarios->count());
            return response()->json($usuarios);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioController@index: ' . $e->getMessage());
            return response()->json(['error' => 'Error interno del servidor'], 500);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Solo los admins pueden crear usuarios
        $currentUser = $request->user();
        if (!$currentUser || $currentUser->rol !== 'admin') {
            return response()->json([
                'message' => 'No tienes permisos para crear usuarios. Solo los administradores pueden hacerlo.',
                'status' => false
            ], 403);
        }

        $validated = $request->validate([
            'nombre' => 'required|string|max:150',
            'email' => 'required|email|max:150|unique:usuarios,email',
            'password' => 'required|string|min:6',
            'rol' => 'required|string',
        ]);

        /**
         * Validar que el rol sea 'admin' o 'usuario'
         */
        if (!in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".',
                'status' => false
            ], 400);
        }

        $validated['password'] = Hash::make($validated['password']);

        try {
            $usuario = Usuario::create($validated);
            return response()->json([
                'message' => 'Usuario creado correctamente',
                'usuario' => $usuario,
                'status' => true
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error al crear usuario: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al crear el usuario',
                'status' => false
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        try {
            $usuario = Usuario::findOrFail($id);
            return response()->json($usuario);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioController@show: ' . $e->getMessage());
            return response()->json(['error' => 'Usuario no encontrado'], 404);
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);

        if ($usuario->rol !== 'admin') {
            return response()->json([
                'message' => 'Solo los usuarios con rol admin pueden ser actualizados.',
                'status' => false
            ], 422);
        }

        $validated = $request->validate([
            'nombre' => 'sometimes|required|string|max:150',
            'email' => 'sometimes|required|email|max:150|unique:usuarios,email,' . $usuario->id,
            'password' => 'nullable|string|min:6',
            'rol' => 'sometimes|required|string',
        ]);

        // Mensaje claro si mandan un rol inválido
        if (isset($validated['rol']) && !in_array($validated['rol'], ['admin', 'usuario'])) {
            return response()->json([
                'message' => 'El rol ingresado no es válido, debe ser "admin" o "usuario".'
            ], 422);
        }

        if (!empty($validated['password'])) {
            $validated['password'] = Hash::make($validated['password']);
        } else {
            unset($validated['password']); // no sobrescribir con null
        }

        $usuario->update($validated);

        return response()->json([
            'message' => 'Usuario actualizado correctamente',
            'data' => $usuario
        ], 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        // Validar autenticación
        $authResponse = $this->validateAuthentication($request);
        if ($authResponse) {
            return $authResponse;
        }

        // Solo los admins pueden eliminar usuarios
        $currentUser = $request->user();
        if ($currentUser->rol !== 'admin') {
            return response()->json([
                'message' => 'No tienes permisos para eliminar usuarios. Solo los administradores pueden hacerlo.',
                'status' => false
            ], 403);
        }

        try {
            $usuario = Usuario::findOrFail($id);
            
            // Prevenir que un admin se elimine a sí mismo
            if ($usuario->id == $currentUser->id) {
                return response()->json([
                    'message' => 'No puedes eliminarte a ti mismo.',
                    'status' => false
                ], 422);
            }
            
            // Eliminar usando la relación del modelo para evitar problemas de FK
            $usuario->tareas()->delete();
            
            $usuario->delete();
            
            return response()->json([
                'message' => 'Usuario eliminado correctamente',
                'status' => true
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json([
                'message' => 'Usuario no encontrado',
                'status' => false
            ], 404);
        } catch (\Exception $e) {
            \Log::error('Error en UsuarioController@destroy: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Error al eliminar el usuario: ' . $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
}
