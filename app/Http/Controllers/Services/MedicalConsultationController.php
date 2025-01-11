<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StoreMedicalConsultationRequest;
use App\Http\Requests\Services\UpdateMedicalConsultationRequest;
use App\Services\Services\MedicalConsultationService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MedicalConsultationController extends Controller
{
    public function __construct(protected MedicalConsultationService $mcService) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $medicalConsultations = $this->mcService->getAll();
        return Inertia::render('Services/MedicalConsultations/Index', [
            'medicalConsultations' => $medicalConsultations
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Services/MedicalConsultations/Form', [
            'formAction' => 'create'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreMedicalConsultationRequest $request)
    {
        $this->mcService->create($request->validated());
        return redirect()->route('medical-consultations.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $medCons = $this->mcService->getById($id);
        $medCons->load('pet:id,name,customer_id,breed_id', 'pet.owner:id,first_name,last_name,ci', 'pet.breed.specie');
        return Inertia::render('Services/MedicalConsultations/Form', [
            'formAction' => 'edit',
            'medicalConsultation' => $medCons
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateMedicalConsultationRequest $request, string $id)
    {
        $data = $request->validated();
        $this->mcService->update($data, $id);
        return redirect()->route('medical-consultations.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
