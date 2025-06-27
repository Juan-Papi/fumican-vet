<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreMedicalConsultationRequest;
use App\Http\Requests\Services\UpdateMedicalConsultationRequest;
use App\Models\Services\Pet;
use App\Services\Services\MedicalConsultationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Illuminate\Http\Request;
use PDF;

class MedicalConsultationController extends Controller
{
    public function __construct(protected MedicalConsultationService $mcService) {}

    public function index(): InertiaResponse
    {
        $medicalConsultations = $this->mcService->getAllWithDetails();
        return Inertia::render('Services/MedicalConsultations/Index', [
            'medicalConsultations' => $medicalConsultations,
            'filters' => [], // Pasar filtros vacíos en la carga inicial
        ]);
    }

    public function search(Request $request): InertiaResponse
    {
        $filters = $request->only('search_term', 'date_from', 'date_to');
        $medicalConsultations = $this->mcService->search($filters);

        return Inertia::render('Services/MedicalConsultations/Index', [
            'medicalConsultations' => $medicalConsultations,
            'filters' => $filters, // Devolver los filtros a la vista
        ]);
    }

    public function store(StoreMedicalConsultationRequest $request): JsonResponse
    {
        $consultation = $this->mcService->create($request->validated());
        return response()->json([
            'message' => 'Consulta creada correctamente.',
            'consultation' => $consultation,
        ], 201);
    }

    public function update(UpdateMedicalConsultationRequest $request, string $id): JsonResponse
    {
        $this->mcService->update($request->validated(), $id);
        return response()->json([
            'message' => 'Consulta actualizada correctamente.'
        ]);
    }

    public function destroy(string $id): JsonResponse
    {
        $this->mcService->delete($id);
        return response()->json([
            'message' => 'Consulta eliminada correctamente.'
        ]);
    }

    public function generateConsultationsReport(Request $request)
    {
        $filters = $request->only('search_term', 'date_from', 'date_to');
        $consultations = $this->mcService->getFilteredResults($filters); // Método sin paginación

        $pdf = Pdf::loadView('pdf.consultations_report', compact('consultations', 'filters'));
        return $pdf->stream('reporte_consultas_' . now()->format('Ymd') . '.pdf');
    }

    // AÑADIDO: Generar historial clínico por mascota
    public function generatePetHistoryReport(Pet $pet)
    {
        // Cargar las relaciones necesarias para el historial
        $pet->load([
            // CORREGIDO: Se cambió 'phone' por 'phone_number' y se eliminó 'address'.
            'owner:id,first_name,last_name,ci,phone_number',
            'breed.specie',
            'medicalConsultations' => function ($query) {
                $query->orderBy('created_at', 'desc')->with('user:id,first_name,last_name');
            }
        ]);

        $pdf = Pdf::loadView('pdf.pet_clinical_history', compact('pet'));
        return $pdf->setPaper('a4', 'portrait')->stream('historial_clinico_' . $pet->name . '.pdf');
    }
}
