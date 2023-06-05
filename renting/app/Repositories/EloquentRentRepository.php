<?php

namespace App\Repositories;

use App\Models\Rent;
use Illuminate\Support\Facades\DB;

/**
 * @method Rent getModel()
 */
class EloquentRentRepository extends EloquentRepository implements RentRepository
{
    public function __construct()
    {
        parent::__construct(Rent::class);
    }

    /** @override */
    public function create(array $data): mixed
    {
        return DB::transaction(function () use ($data) {
            $items = $data['items'];

            $rent = $this->getModel()->create($data);
            $this->createItems($rent->id, $items);

            return $rent;
        });
    }

    /** @override */
    public function update(string $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            $rent = $this->find($id);
            $rent->update($data);

            $items = $data['items'];
            $rent->items()->delete();
            $this->createItems($id, $items);

            return $rent;
        });
    }

    public function createItems(string $rentId, array $items): mixed
    {
        $rent = $this->find($rentId);
        return $rent->items()->createMany($items);
    }
}
