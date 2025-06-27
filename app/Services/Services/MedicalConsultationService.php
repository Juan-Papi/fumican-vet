<?php

namespace App\Services\Services;

use App\Repositories\Services\MedicalConsultationRepository;

class MedicalConsultationService
{
    public function __construct(protected MedicalConsultationRepository $repository) {}

    public function getAllWithDetails()
    {
        $medicalConsultations = $this->repository->getAllWithDetails();
        // Llamar al método privado para añadir detalles
        return $this->addPetDetailsToConsultations($medicalConsultations);
    }

    public function create(array $data)
    {
        return $this->repository->create($data);
    }

    public function update(array $data, $id)
    {
        return $this->repository->update($data, $id);
    }

    public function delete($id)
    {
        return $this->repository->delete($id);
    }

    public function search(array $filters)
    {
        $medicalConsultations = $this->repository->search($filters, true); // Paginated
        // Llamar al método privado para añadir detalles
        return $this->addPetDetailsToConsultations($medicalConsultations);
    }

    public function getFilteredResults(array $filters)
    {
        $consultations = $this->repository->search($filters, false); // Not paginated
        // Asegurarse de que los resultados para el PDF también tengan los detalles
        return $this->addPetDetailsToConsultations($consultations);
    }

    /**
     * AÑADIDO: Método privado para centralizar la lógica de añadir detalles.
     * Funciona tanto para colecciones paginadas como para colecciones normales.
     */
    private function addPetDetailsToConsultations($consultations)
    {
        foreach ($consultations as $mc) {
            $mc->pet_name = $mc->pet?->name ?? 'N/A';
            $mc->pet_owner = $mc->pet?->owner ? ($mc->pet->owner->first_name . ' ' . $mc->pet->owner->last_name) : 'N/A';
        }
        return $consultations;
    }
}
