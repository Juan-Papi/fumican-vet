<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StorePetRequest;
use App\Http\Requests\Services\UpdatePetRequest;
use App\Services\Services\BreedService;
use App\Services\Services\PetService;
use App\Services\Services\SpecieService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use App\Models\Services\Pet;

class PetController extends Controller
{
    public function __construct(protected PetService $service) {}

    public function index(): InertiaResponse
    {
        return Inertia::render('Services/Pets/Index', [
            'pets' => $this->service->getAll(),
            'filters' => [],
        ]);
    }

    public function search(Request $request): InertiaResponse
    {
        $filters = $request->only('search_term');
        return Inertia::render('Services/Pets/Index', [
            'pets' => $this->service->search($filters),
            'filters' => $filters,
        ]);
    }

    public function store(StorePetRequest $request): JsonResponse
    {
        $pet = $this->service->create($request->validated());
        return response()->json(['message' => 'Mascota registrada correctamente.', 'pet' => $pet], 201);
    }

    public function update(UpdatePetRequest $request, Pet $pet): JsonResponse
    {
        $this->service->update($pet->id, $request->validated());
        return response()->json(['message' => 'Mascota actualizada correctamente.']);
    }

    public function destroy(Pet $pet): JsonResponse
    {
        $this->service->delete($pet->id);
        return response()->json(['message' => 'Mascota eliminada correctamente.']);
    }

    public function autocomplete(Request $request): JsonResponse
    {
        $pets = $this->service->autocompleteSearch($request->search);
        return response()->json($pets);
    }

    public function prepareStoreData(Request $request, SpecieService $specieService, BreedService $breedService): JsonResponse
    {
        try {
            $specie = $specieService->findOrCreate($request->specie);
            $breed = $breedService->findOrCreate($request->breed, $specie->id);
            return response()->json(['specie_id' => $specie->id, 'breed_id' => $breed->id]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }
}
