<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tenant;
use App\Services\TenantService;

/**
 * Controlador TenantController para demostración de arquitectura multitenant
 * 
 * NOTA: Este controlador es solo para demostración académica.
 * NO tiene rutas registradas y NO afecta la funcionalidad existente.
 */
class TenantController extends Controller
{
    protected $tenantService;

    public function __construct(TenantService $tenantService)
    {
        $this->tenantService = $tenantService;
    }

    /**
     * Listar todos los tenants (solo para super admin)
     */
    public function index()
    {
        try {
            $tenants = Tenant::with(['usuarios', 'tareas'])->get();
            
            return response()->json([
                'tenants' => $tenants,
                'total' => $tenants->count(),
                'status' => true
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener tenants',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Crear un nuevo tenant
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255',
                'subdominio' => 'required|string|max:50|unique:tenants,subdominio',
                'database_username' => 'required|string|max:255',
                'database_password' => 'required|string|min:8',
                'database_host' => 'nullable|string|max:255',
                'database_port' => 'nullable|integer',
                'fecha_expiracion' => 'nullable|date',
                'configuracion' => 'nullable|array'
            ]);

            $tenant = $this->tenantService->createTenant($validated);

            return response()->json([
                'message' => 'Tenant creado exitosamente',
                'tenant' => $tenant,
                'status' => true
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear tenant',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Mostrar información de un tenant específico
     */
    public function show($id)
    {
        try {
            $tenant = Tenant::with(['usuarios', 'tareas'])->findOrFail($id);

            return response()->json([
                'tenant' => $tenant,
                'stats' => [
                    'usuarios_count' => $tenant->usuarios->count(),
                    'tareas_count' => $tenant->tareas->count(),
                    'active' => $tenant->isActive()
                ],
                'status' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Tenant no encontrado',
                'error' => $e->getMessage(),
                'status' => false
            ], 404);
        }
    }

    /**
     * Actualizar un tenant
     */
    public function update(Request $request, $id)
    {
        try {
            $tenant = Tenant::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'sometimes|required|string|max:255',
                'estado' => 'sometimes|boolean',
                'fecha_expiracion' => 'nullable|date',
                'configuracion' => 'nullable|array'
            ]);

            $tenant->update($validated);

            return response()->json([
                'message' => 'Tenant actualizado exitosamente',
                'tenant' => $tenant,
                'status' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar tenant',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Eliminar un tenant
     */
    public function destroy($id)
    {
        try {
            $tenant = Tenant::findOrFail($id);
            
            $this->tenantService->deleteTenant($tenant);

            return response()->json([
                'message' => 'Tenant eliminado exitosamente',
                'status' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar tenant',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Obtener información del tenant actual
     */
    public function current()
    {
        try {
            $tenant = $this->tenantService->getCurrentTenant();

            if (!$tenant) {
                return response()->json([
                    'message' => 'No hay tenant activo',
                    'tenant' => null,
                    'status' => false
                ], 404);
            }

            return response()->json([
                'tenant' => $tenant,
                'active' => $tenant->isActive(),
                'stats' => [
                    'usuarios_count' => $tenant->usuarios->count(),
                    'tareas_count' => $tenant->tareas->count()
                ],
                'status' => true
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al obtener tenant actual',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }

    /**
     * Verificar disponibilidad de subdominio
     */
    public function checkSubdomain(Request $request)
    {
        $subdomain = $request->input('subdomain');
        
        $exists = Tenant::where('subdominio', $subdomain)->exists();
        
        return response()->json([
            'subdomain' => $subdomain,
            'available' => !$exists,
            'status' => true
        ]);
    }
}