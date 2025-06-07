<?php

namespace App\Services\Sales;

use App\Models\Sales\SalesNote;
use App\Models\Sales\SalesNoteDetail;
use App\Repositories\Sales\SalesNoteRepository;
use Illuminate\Support\Facades\DB;

class SalesNoteService
{
    public function __construct(protected SalesNoteRepository $salesNoteRepository, protected InventoryService $inventoryService) {}

    public function getAllSalesNote()
    {
        return $this->salesNoteRepository->getAll();
    }

    public function getSalesNoteById($id)
    {
        return $this->salesNoteRepository->findById($id);
    }

    public function createSalesNoteDetail(array $salesNoteData)
    {
        return $this->salesNoteRepository->create($salesNoteData);
    }

    public function createSalesNoteWithInventory(array $salesNoteData)
    {
        DB::beginTransaction();
        try {
            // Crear la nota de venta
            $salesNote = SalesNote::create($salesNoteData);

            foreach ($salesNoteData['medicaments'] as $medicament) {
                // Crear el detalle de la venta
                $saleDetailData = [
                    'sales_note_id' => $salesNote->id,
                    'medicament_id' => $medicament['id'],
                    'quantity'      => $medicament['quantity'],
                    'sale_price'    => $medicament['sale_price'],
                    'subtotal'      => $medicament['subtotal'],
                ];
                $salesNoteDetail = SalesNoteDetail::create($saleDetailData);

                // 2.2) Consumir stock usando FIFO
                $this->inventoryService->consumeStock(
                    $salesNoteData['warehouse_id'],
                    $medicament['id'],
                    $medicament['quantity'],
                    $salesNoteDetail->id

                );
            }

            DB::commit();
            return $salesNote;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function updateSalesNoteWithInventory(int $id, array $data)
    {
        DB::beginTransaction();
        try {
            $salesNote = SalesNote::findOrFail($id);
            $salesNote->update([
                'customer_id'  => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'total_amount' => $data['total_amount'],
            ]);

            // 1) Restaurar stock y borrar detalles viejos
            foreach ($salesNote->salesNoteDetails as $old) {
                $this->inventoryService->restoreStockForSalesDetail($old->id);
                $old->delete();
            }

            // 2) Crear nuevos detalles y consumir stock FIFO
            foreach ($data['medicaments'] as $m) {
                $detail = SalesNoteDetail::create([
                    'sales_note_id' => $id,
                    'medicament_id' => $m['id'],
                    'quantity'      => $m['quantity'],
                    'sale_price'    => $m['sale_price'],
                    'subtotal'      => $m['subtotal'],
                ]);
                $this->inventoryService->consumeStock(
                    $salesNote->warehouse_id,
                    $m['id'],
                    $m['quantity'],
                    $detail->id
                );
            }

            DB::commit();
            return $salesNote;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
