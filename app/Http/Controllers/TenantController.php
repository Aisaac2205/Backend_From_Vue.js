<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class TenantController extends Controller
{
    /**
     * Crear un nuevo tenant con su base de datos
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tenant_id' => 'required|string|max:50|regex:/^[a-zA-Z0-9_-]+$/|unique:tenants,tenant_id',
            'name' => 'required|string|max:255',
            'subdomain' => 'required|string|max:50|regex:/^[a-zA-Z0-9_-]+$/|unique:tenants,subdomain',
            'domain' => 'nullable|string|max:255',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tenant = Tenant::createWithDatabase($request->all());

            Log::info('Nuevo tenant creado exitosamente', [
                'tenant_id' => $tenant->tenant_id,
                'subdomain' => $tenant->subdomain,
                'database' => $tenant->database_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tenant creado exitosamente',
                'data' => [
                    'tenant_id' => $tenant->tenant_id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'domain' => $tenant->domain,
                    'status' => $tenant->status,
                    'access_url' => "https://{$tenant->subdomain}." . env('BASE_DOMAIN')
                ]
            ], 201);

        } catch (\Exception $e) {
            Log::error('Error creando tenant', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor al crear tenant',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Listar todos los tenants
     */
    public function index()
    {
        try {
            $tenants = Tenant::select('id', 'tenant_id', 'name', 'subdomain', 'domain', 'status', 'created_at')
                            ->orderBy('created_at', 'desc')
                            ->get()
                            ->map(function ($tenant) {
                                return [
                                    'id' => $tenant->id,
                                    'tenant_id' => $tenant->tenant_id,
                                    'name' => $tenant->name,
                                    'subdomain' => $tenant->subdomain,
                                    'domain' => $tenant->domain,
                                    'status' => $tenant->status,
                                    'access_url' => "https://{$tenant->subdomain}." . env('BASE_DOMAIN'),
                                    'created_at' => $tenant->created_at
                                ];
                            });

            return response()->json([
                'success' => true,
                'data' => $tenants
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo tenants',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Obtener un tenant específico
     */
    public function show($tenantId)
    {
        try {
            $tenant = Tenant::where('tenant_id', $tenantId)->first();

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant no encontrado'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $tenant->id,
                    'tenant_id' => $tenant->tenant_id,
                    'name' => $tenant->name,
                    'subdomain' => $tenant->subdomain,
                    'domain' => $tenant->domain,
                    'database_name' => $tenant->database_name,
                    'status' => $tenant->status,
                    'settings' => $tenant->settings,
                    'access_url' => "https://{$tenant->subdomain}." . env('BASE_DOMAIN'),
                    'created_at' => $tenant->created_at,
                    'updated_at' => $tenant->updated_at
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error obteniendo tenant',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Actualizar tenant (sin BD)
     */
    public function update(Request $request, $tenantId)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'domain' => 'nullable|string|max:255',
            'status' => 'sometimes|required|in:active,inactive,suspended',
            'settings' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Datos de validación incorrectos',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $tenant = Tenant::where('tenant_id', $tenantId)->first();

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant no encontrado'
                ], 404);
            }

            $tenant->update($request->only(['name', 'domain', 'status', 'settings']));

            return response()->json([
                'success' => true,
                'message' => 'Tenant actualizado exitosamente',
                'data' => [
                    'tenant_id' => $tenant->tenant_id,
                    'name' => $tenant->name,
                    'status' => $tenant->status
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error actualizando tenant',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }

    /**
     * Eliminar tenant y su base de datos
     */
    public function destroy($tenantId)
    {
        try {
            $tenant = Tenant::where('tenant_id', $tenantId)->first();

            if (!$tenant) {
                return response()->json([
                    'success' => false,
                    'message' => 'Tenant no encontrado'
                ], 404);
            }

            $tenant->deleteWithDatabase();

            Log::info('Tenant eliminado exitosamente', [
                'tenant_id' => $tenantId,
                'database' => $tenant->database_name
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Tenant y su base de datos eliminados exitosamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error eliminando tenant', [
                'tenant_id' => $tenantId,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error eliminando tenant',
                'error' => app()->environment('local') ? $e->getMessage() : 'Error interno'
            ], 500);
        }
    }
}