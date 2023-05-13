<?php

namespace App\Repositories;

use App\Models\Supplier;

class EloquentSupplierRepository extends EloquentRepository implements SupplierRepository
{
    public function __construct()
    {
        parent::__construct(Supplier::class);
    }
}
