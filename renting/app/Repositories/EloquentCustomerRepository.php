<?php

namespace App\Repositories;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Builder;

class EloquentCustomerRepository implements CustomerRepository
{
    /**
     * @var Builder
     */
    private $query;

    public function __construct()
    {
        $this->query = Customer::query();
    }

    public function get(): array
    {
        return $this->query->get()->all();
    }

    public function paginate(int $page, int $per_page): array
    {
        $paginator = $this->query->paginate($per_page, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'items' => $paginator->items(),
        ];
    }

    public function where(mixed $condition, mixed $value = null): Repository
    {
        $this->query->where($condition, $value);
        return $this;
    }

    public function contains(mixed $column, mixed $value = null): Repository
    {
        if (is_array($column)) {
            foreach ($column as $key => $match) {
                $this->contains($key, $match);
            }
        } else {
            if (!empty($value)) {
                $this->query->where($column, 'like', '%' . $value . '%');
            }
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): Repository
    {
        $this->query->orderBy($column, $direction);
        return $this;
    }
}
