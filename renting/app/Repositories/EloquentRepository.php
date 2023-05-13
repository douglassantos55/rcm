<?php

namespace App\Repositories;

use App\Exceptions\DatabaseException;
use App\Exceptions\EntityNotFoundException;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

abstract class EloquentRepository implements Repository
{
    /**
     * @var Builder
     */
    private $query;

    /**
     * @var string
     */
    private $model;

    public function __construct(string $model)
    {
        $this->model = $model;
    }

    public function get(): array
    {
        return $this->getQuery()->get()->all();
    }

    /**
     * @return Model
     */
    public function find(string $id): mixed
    {
        $instance = $this->getModel()->newQuery()->find($id);
        if (is_null($instance)) {
            throw new EntityNotFoundException();
        }
        return $instance;
    }

    public function create(array $data): mixed
    {
        $instance = $this->getModel()->newInstance($data);
        $instance->save();

        return $instance;
    }

    public function update(string $id, array $data): mixed
    {
        $entity = $this->find($id);
        if (!$entity->update($data)) {
            throw new DatabaseException('Could not update');
        }
        return $entity;
    }

    public function delete(string $id): bool
    {
        return $this->find($id)->delete();
    }

    public function paginate(int $page, int $per_page): array
    {
        $paginator = $this->getQuery()->paginate($per_page, ['*'], 'page', $page);

        return [
            'total' => $paginator->total(),
            'items' => $paginator->items(),
        ];
    }

    public function where(mixed $condition, mixed $value = null): Repository
    {
        $this->getQuery()->where($condition, $value);
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
                $this->getQuery()->where($column, 'like', '%' . $value . '%');
            }
        }
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): Repository
    {
        $this->getQuery()->orderBy($column, $direction);
        return $this;
    }

    private function getQuery(): Builder
    {
        if (is_null($this->query)) {
            $this->query = $this->getModel()->newQuery();
        }
        return $this->query;
    }

    private function getModel(): Model
    {
        $instance = app()->make($this->model);
        if (!$instance instanceof Model) {
            throw new \Exception($this->model . ' is not an instance of Model');
        }
        return $instance;
    }
}
