<?php

namespace App\Repositories\Sales;

use App\Models\Sales\SalesNote;

class SalesNoteRepository
{

    public function getAll()
    {
        return SalesNote::with(['warehouse', 'user', 'customer', 'salesNoteDetails.medicament'])
            ->orderBy('updated_at', 'desc')
            ->paginate(15);
    }


    public function findById($id)
    {
        return SalesNote::with(['warehouse', 'user', 'customer', 'salesNoteDetails.medicament'])->findOrFail($id);
    }

    public function create(array $salesNoteData)
    {
        return SalesNote::create($salesNoteData);
    }

    public function update($id, array $data)
    {
        return SalesNote::where('id', $id)->update($data);
    }
}
