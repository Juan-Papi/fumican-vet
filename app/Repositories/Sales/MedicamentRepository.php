<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Medicament;

class MedicamentRepository
{

    public function getAll()
    {
        return Medicament::orderBy('updated_at', 'desc')
            ->with('category')
            ->paginate();
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

    public function search()
    {
        $search = request('search');
        return Medicament::where(function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('dosage', 'like', "%$search%")
                ->orWhere('manufacturer', 'like', "%$search%");
        })
            ->orWhereHas('category', function ($query) use ($search) {
                $query->where('name', 'like', "%$search%");
            })
            ->take(5)
            ->get();
    }

    public function delete($id)
    {
        return Medicament::destroy($id);
    }
}
