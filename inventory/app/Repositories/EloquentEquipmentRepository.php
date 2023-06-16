<?php

namespace App\Repositories;

use App\Models\Equipment;

class EloquentEquipmentRepository extends EloquentRepository implements EquipmentRepository
{
    public function __construct()
    {
        parent::__construct(Equipment::class);
    }
}
