<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Supplier;

class SupplierRepository
{

    public function getAll()
    {
        return Supplier::orderBy('updated_at', 'desc')->paginate();
    }

    public function findById($id)
    {
        return Supplier::findOrFail($id);
    }

    public function create(array $userData)
    {
        return Supplier::create($userData);
    }

    public function update($id, array $data)
    {
        return Supplier::where('id', $id)->update($data);
    }

    public function search()
    {
        $search = request('search');
        return Supplier::where(function ($query) use ($search) {
            $query->whereLike('name', "%$search%")
                ->orWhereLike('country', "%$search%")
                ->orWhereLike('phoneNumber', "%$search%")
                ->orWhereLike('email', "%$search%")
                ->orWhereLike('address', "%$search%");
        })
            ->take(5)
            ->get();
    }
}
