<?php

namespace App\Repositories;

use App\Models\Customer;

class EloquentCustomerRepository extends EloquentRepository implements CustomerRepository
{
    public function __construct()
    {
        parent::__construct(Customer::class);
    }
}
