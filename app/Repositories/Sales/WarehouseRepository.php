<?php

namespace App\Repositories\Sales;

use App\Models\Sales\Warehouse;

class WarehouseRepository
{

    public function getAll()
    {
        return Warehouse::orderBy('updated_at', 'desc')->paginate();
    }

    public function getAllWithoutPaginate()
    {
        return Warehouse::orderBy('updated_at', 'desc')->get();
    }


    public function findById($id)
    {
        return Warehouse::findOrFail($id);
    }

    public function create(array $userData)
    {
        return Warehouse::create($userData);
    }

    public function update($id, array $data)
    {
        return Warehouse::where('id', $id)->update($data);
    }

    public function search()
    {
        $search = request('search');
        return Warehouse::where(function ($query) use ($search) {
            $query->where('name', 'like', "%$search%")
                ->orWhere('location', 'like', "%$search%")
                ->orWhere('description', 'like', "%$search%");
        })
            ->take(5)
            ->get();
    }

    public function getGroupedInventories($id)
    {
        $warehouse = Warehouse::with([
            'inventories' => function ($query) {
                $query->selectRaw('medicament_id, sum(stock) as total_stock')
                    ->groupBy('medicament_id');
            }
        ])->findOrFail($id);

        // Verificar si el almacén tiene inventarios
        if ($warehouse->inventories->isEmpty()) {
            dd('No inventories found for this warehouse.');
        }

        return $warehouse;
    }
}
