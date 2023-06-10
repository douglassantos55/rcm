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
            $this->createItems($rent, $items);

            return $rent;
        });
    }

    /** @override */
    public function update(string $id, array $data): mixed
    {
        return DB::transaction(function () use ($id, $data) {
            /** @var Rent */
            $rent = $this->find($id);
            $rent->update($data);

            $rent->items()->delete();
            $this->createItems($rent, $data['items']);

            return $rent->fresh();
        });
    }

    public function createItems(mixed $rent, array $items): mixed
    {
        return $rent->items()->createMany($items);
    }
}
