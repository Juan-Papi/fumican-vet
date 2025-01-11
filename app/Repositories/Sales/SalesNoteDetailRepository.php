<?php

namespace App\Repositories\Sales;

use App\Models\Sales\SalesNoteDetail;

class SalesNoteDetailRepository
{

    public function getAll()
    {
        return SalesNoteDetail::orderBy('updated_at', 'desc')
            ->paginate();
    }


    public function findById($id)
    {
        return SalesNoteDetail::findOrFail($id);
    }

    public function create(array $salesNoteDetailData)
    {
        return SalesNoteDetail::create($salesNoteDetailData);
    }

    public function update($id, array $data)
    {
        return SalesNoteDetail::where('id', $id)->update($data);
    }

    public function findBySalesNoteId($salesNoteId)
    {
        return SalesNoteDetail::where('sales_note_id', $salesNoteId)->with('medicament')->get();
    }
}
