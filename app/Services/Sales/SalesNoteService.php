<?php

namespace App\Services\Sales;

use App\Models\Sales\Inventory;
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
                    $salesNoteData['warehouse_id'],    // almacén
                    $medicament['id'],                 // id de medicamento
                    $medicament['quantity'],           // cantidad vendida
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
            // 1) Actualizar datos de la nota
            $salesNote = SalesNote::findOrFail($id);
            $salesNote->update([
                'customer_id'  => $data['customer_id'],
                'warehouse_id' => $data['warehouse_id'],
                'total_amount' => $data['total_amount'],
            ]);

            // 2) Obtener detalles originales
            $originalDetails = $salesNote->salesNoteDetails()->get()->keyBy('medicament_id');

            // 3) Recorrer los medicaments enviados
            foreach ($data['medicaments'] as $m) {
                $medId = $m['id'];
                $qty   = $m['quantity'];

                if ($originalDetails->has($medId)) {
                    // a) Ajuste de inventario: diferencia
                    $diff = $qty - $originalDetails[$medId]->quantity;
                    $inv = $this->inventoryService
                        ->getByMedicamentAndWarehouse($medId, $salesNote->warehouse_id);
                    if ($inv) {
                        $this->inventoryService->adjustStock($inv->id, -$diff);
                    }
                    // b) Actualizar detalle
                    $originalDetails[$medId]->update([
                        'quantity'   => $qty,
                        'sale_price' => $m['sale_price'],
                        'subtotal'   => $m['subtotal'],
                    ]);
                    $originalDetails->forget($medId);
                } else {
                    // Detalle nuevo
                    $newDetail = SalesNoteDetail::create([
                        'sales_note_id' => $id,
                        'medicament_id' => $medId,
                        'quantity'      => $qty,
                        'sale_price'    => $m['sale_price'],
                        'subtotal'      => $m['subtotal'],
                    ]);
                    // Reducir inventario
                    $inv = $this->inventoryService
                        ->getByMedicamentAndWarehouse($medId, $salesNote->warehouse_id);
                    if ($inv) {
                        $this->inventoryService->adjustStock($inv->id, -$qty);
                    }
                }
            }

            // 4) Los que quedaron en $originalDetails fueron eliminados en el form → restaurar stock y borrar
            foreach ($originalDetails as $detail) {
                $inv = $this->inventoryService
                    ->getByMedicamentAndWarehouse($detail->medicament_id, $salesNote->warehouse_id);
                if ($inv) {
                    $this->inventoryService->adjustStock($inv->id, $detail->quantity);
                }
                $detail->delete();
            }

            DB::commit();
            return $salesNote;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
}
