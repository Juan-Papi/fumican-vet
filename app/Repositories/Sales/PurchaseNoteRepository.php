<?php

namespace App\Repositories\Sales;

use App\Models\Sales\PurchaseNote;

class PurchaseNoteRepository
{
    public function getAll(array $filters = [], bool $paginate = true)
    {
        $q = PurchaseNote::with(['warehouse', 'supplier', 'user'])
            ->orderBy('updated_at', 'desc');

        // -------- filtros ----------
        if (!empty($filters['supplier_id'])) {
            $q->where('supplier_id', $filters['supplier_id']);
        }
        if (!empty($filters['warehouse_id'])) {
            $q->where('warehouse_id', $filters['warehouse_id']);
        }
        if (!empty($filters['date_from'])) {
            $q->whereDate('purchase_date', '>=', $filters['date_from']);
        }
        if (!empty($filters['date_to'])) {
            $q->whereDate('purchase_date', '<=', $filters['date_to']);
        }
        // -----------------------------

        if ($paginate) {
            return $q->paginate(15)->appends($filters);   // mantiene query-string
        }

        return $q->get();
    }


    public function findById($id)
    {
        return PurchaseNote::with(['warehouse', 'supplier', 'user', 'purchaseNoteDetails.medicament'])->findOrFail($id);
    }

    public function create(array $purchaseNoteData)
    {
        try {
            $purchase = PurchaseNote::create($purchaseNoteData);
            return $purchase;
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function update($id, array $data)
    {
        return PurchaseNote::where('id', $id)->update($data);
    }


    public function deleteById($id)
    {
        return PurchaseNote::where('id', $id)->delete();
    }
}
