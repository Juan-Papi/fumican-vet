<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreMedicalConsultationRequest;
use App\Http\Requests\Services\UpdateMedicalConsultationRequest;
use App\Services\Services\MedicalConsultationService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class MedicalConsultationController extends Controller
{
    public function __construct(protected MedicalConsultationService $mcService) {}

    public function index(): InertiaResponse
    {
        $medicalConsultations = $this->mcService->getAllWithDetails();
        return Inertia::render('Services/MedicalConsultations/Index', [
            'medicalConsultations' => $medicalConsultations,
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
}
