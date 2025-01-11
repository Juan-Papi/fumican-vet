<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Http\Requests\Services\StorePetRequest;
use App\Http\Requests\Services\UpdatePetRequest;
use App\Services\Services\BreedService;
use App\Services\Services\PetService;
use App\Services\Services\SpecieService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PetController extends Controller
{
    public function __construct(protected PetService $service) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $pets = $this->service->getAll();
        return Inertia::render('Services/Pets/Index', ['pets' => $pets]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Services/Pets/Form', [
            'formAction' => 'create'
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePetRequest $request): RedirectResponse
    {
        $this->service->create($request->validated());
        return redirect()->route('pets.index');
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
        $pet = $this->service->getById($id);
        $pet->load(['breed.specie', 'owner']);
        return Inertia::render('Services/Pets/Form', [
            'formAction' => 'edit',
            'pet' => $pet
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePetRequest $request, string $id)
    {
        $data = $request->validated();
        $this->service->update($id, $data);
        return redirect()->route('pets.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    /**
     * Search for a specific resource.
     */
    public function search(Request $request)
    {
        $pets = $this->service->search($request->search);
        return response()->json($pets);
    }

    /**
     * Prepare data to store a resource.Create a breed and/or specie if they don't exist.
     */
    public function prepareStoreData(Request $request, SpecieService $specieService, BreedService $breedService)
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
