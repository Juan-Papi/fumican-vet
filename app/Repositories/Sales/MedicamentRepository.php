<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Medicament;

class MedicamentRepository
{

    public function getAll()
    {
        return Medicament::orderBy('updated_at', 'desc')
            ->with('category')
            ->paginate(15);
    }

    public function findById($id)
    {
        return Medicament::findOrFail($id);
    }

    public function create(array $userData)
    {
        return Medicament::create($userData);
    }

    public function update($id, array $data)
    {
        return Medicament::where('id', $id)->update($data);
    }

    public function search(array $filters)
    {
        $query = Medicament::with('category');

        if (!empty($filters['name'])) {
            $query->where('name', 'like', "%{$filters['name']}%");
        }
        if (!empty($filters['dosage'])) {
            $query->where('dosage', 'like', "%{$filters['dosage']}%");
        }
        if (!empty($filters['manufacturer'])) {
            $query->where('manufacturer', 'like', "%{$filters['manufacturer']}%");
        }
        if (!empty($filters['expiration_from'])) {
            $query->whereDate('expiration_date', '>=', $filters['expiration_from']);
        }
        if (!empty($filters['expiration_to'])) {
            $query->whereDate('expiration_date', '<=', $filters['expiration_to']);
        }
        if (isset($filters['controlled_substance']) && $filters['controlled_substance'] !== '') {
            $query->where('controlled_substance', $filters['controlled_substance']);
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        $perPage = $filters['per_page'] ?? 15;

        return $query
            ->orderBy('updated_at', 'desc')
            ->paginate($perPage)
            ->appends($filters);
    }

    public function delete($id)
    {
        return Medicament::destroy($id);
    }
}
