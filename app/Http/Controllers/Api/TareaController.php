<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tarea;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class TareaController extends Controller
{
    /**
     * Validar que el usuario esté autenticado
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
        return response()->json(Tarea::with('usuario')->get());
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
     * 
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request)
    {
        // Validación mejorada con mensajes personalizados
        $validated = $request->validate([
            'titulo' => 'required|string|max:200',
            'descripcion' => 'nullable|string',
            'estado' => 'in:pendiente,en_progreso,completada',
            'fecha_vencimiento' => 'nullable|date',
            'usuario_id' => 'required|exists:usuarios,id'
        ]);

        $tarea = Tarea::create($validated);
        
        return response()->json([
            'message' => 'Tarea creada correctamente',
            'data' => $tarea->load('usuario'),
            'status' => true
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $tarea = Tarea::with('usuario')->find($id);
        
        if (!$tarea) {
            return response()->json([
                'message' => 'Tarea no encontrada',
                'status' => false
            ], 404);
        }
        
        return response()->json($tarea);
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
        $tarea = Tarea::find($id);
        
        if (!$tarea) {
            return response()->json([
                'message' => 'Tarea no encontrada',
                'status' => false
            ], 404);
        }

        $validated = $request->validate([
            'titulo' => 'string|max:200',
            'descripcion' => 'nullable|string',
            'estado' => 'in:pendiente,en_progreso,completada',
            'fecha_vencimiento' => 'nullable|date',
            'usuario_id' => 'exists:usuarios,id'
        ]);

        $tarea->update($validated);
        
        return response()->json([
            'message' => 'Tarea actualizada correctamente',
            'data' => $tarea->load('usuario'),
            'status' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, string $id)
    {
        $currentUser = $request->user();
        $tarea = Tarea::find($id);
        
        if (!$tarea) {
            return response()->json([
                'message' => 'Tarea no encontrada',
                'status' => false
            ], 404);
        }

        // Los usuarios pueden eliminar solo sus propias tareas, los admins pueden eliminar cualquier tarea
        if ($currentUser->rol !== 'admin' && $tarea->usuario_id !== $currentUser->id) {
            return response()->json([
                'message' => 'No tienes permisos para eliminar esta tarea',
                'status' => false
            ], 403);
        }

        $tarea->delete();
        
        return response()->json([
            'message' => 'Tarea eliminada correctamente',
            'status' => true
        ]);
    }

    /**
     * Update task status (for admin)
     */
    public function updateStatus(Request $request, string $id)
    {
        $currentUser = $request->user();
        
        // Solo los administradores pueden cambiar el estado desde la vista de administración
        if ($currentUser->rol !== 'admin') {
            return response()->json([
                'message' => 'No tienes permisos para cambiar el estado de tareas',
                'status' => false
            ], 403);
        }

        $tarea = Tarea::find($id);
        
        if (!$tarea) {
            return response()->json([
                'message' => 'Tarea no encontrada',  
                'status' => false
            ], 404);
        }

        $validated = $request->validate([
            'estado' => 'required|in:pendiente,en_progreso,completada'
        ]);

        $tarea->update($validated);
        
        return response()->json([
            'message' => 'Estado de tarea actualizado correctamente',
            'data' => $tarea->load('usuario'),
            'status' => true
        ]);
    }

    /**
     * Generate and download Excel report of tasks
     */
    public function reporteExcel(Request $request)
    {
        try {
            // Obtener todas las tareas con sus usuarios
            $tareas = Tarea::with('usuario')->get();

            // Crear una nueva hoja de cálculo
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Configurar el título de la hoja
            $sheet->setTitle('Reporte de Tareas');

            // Establecer encabezados
            $headers = [
                'A1' => 'ID',
                'B1' => 'Título',
                'C1' => 'Descripción',
                'D1' => 'Estado',
                'E1' => 'Fecha de Vencimiento',
                'F1' => 'Usuario Asignado',
                'G1' => 'Email del Usuario',
                'H1' => 'Fecha de Creación',
                'I1' => 'Última Actualización'
            ];

            foreach ($headers as $cell => $value) {
                $sheet->setCellValue($cell, $value);
            }

            // Aplicar estilo a los encabezados
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '4A90E2'],
                ],
                'alignment' => [
                    'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                ],
            ];
            $sheet->getStyle('A1:I1')->applyFromArray($headerStyle);

            // Rellenar los datos
            $row = 2;
            foreach ($tareas as $tarea) {
                $sheet->setCellValue('A' . $row, $tarea->id);
                $sheet->setCellValue('B' . $row, $tarea->titulo);
                $sheet->setCellValue('C' . $row, $tarea->descripcion ?? 'Sin descripción');
                $sheet->setCellValue('D' . $row, ucfirst(str_replace('_', ' ', $tarea->estado)));
                $sheet->setCellValue('E' . $row, $tarea->fecha_vencimiento ? $tarea->fecha_vencimiento->format('d/m/Y') : 'Sin fecha');
                $sheet->setCellValue('F' . $row, $tarea->usuario ? $tarea->usuario->nombre : 'Sin asignar');
                $sheet->setCellValue('G' . $row, $tarea->usuario ? $tarea->usuario->email : 'Sin email');
                $sheet->setCellValue('H' . $row, $tarea->created_at->format('d/m/Y H:i'));
                $sheet->setCellValue('I' . $row, $tarea->updated_at->format('d/m/Y H:i'));
                $row++;
            }

            // Ajustar el ancho de las columnas automáticamente
            foreach (range('A', 'I') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // Aplicar bordes a toda la tabla
            $tableRange = 'A1:I' . ($row - 1);
            $borderStyle = [
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];
            $sheet->getStyle($tableRange)->applyFromArray($borderStyle);

            // Crear el escritor Excel
            $writer = new Xlsx($spreadsheet);

            // Crear nombre de archivo con fecha actual
            $fileName = 'reporte-tareas-' . date('Y-m-d') . '.xlsx';

            // Establecer headers para descarga
            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
                'Cache-Control' => 'max-age=0',
            ];

            // Crear respuesta con el archivo Excel
            return response()->stream(
                function () use ($writer) {
                    $writer->save('php://output');
                },
                200,
                $headers
            );

        } catch (\Exception $e) {
            \Log::error('Error al generar reporte Excel: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al generar el reporte Excel',
                'error' => $e->getMessage(),
                'status' => false
            ], 500);
        }
    }
}
