<?php

namespace App\Services\Reservations;

use App\Models\Sales\Inventory;
use App\Models\Sales\StockMovement;
use App\Repositories\Reservations\ReserveRepository;

class ReserveService
{
    public function __construct(protected ReserveRepository $reserveRepository) {}

   
}
