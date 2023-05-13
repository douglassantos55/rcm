<?php

namespace App\Repositories;

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

interface Repository
{
    /**
     * Get results
     *
     * @return array
     */
    public function get(): array;

    /**
     * Get an entity by ID
     *
     * @param string $id
     *
     * @return mixed The entity
     *
     * @throws NotFoundHttpException
     */
    public function find(string $id): mixed;

    /**
     * Create an entity
     *
     * @param array $data
     *
     * @return mixed The created entity
     */
    public function create(array $data): mixed;

    /**
     * Update an entity
     *
     * @param string $id The ID of the entity to update
     * @param array $data The data to update
     *
     * @return mixed The updated entity
     *
     * @throws NotFoundHttpException
     */
    public function update(string $id, array $data): mixed;

    /**
     * Delete an entity
     *
     * @param string $id The ID of the entity to delete
     *
     * @return bool
     *
     * @throws NotFoundHttpException
     */
    public function delete(string $id): bool;

    /**
     * Paginates results
     *
     * @return array ['total' => int, 'items' => array]
     */
    public function paginate(int $page, int $per_page): array;

    /**
     * Filters results where condition matches value exactly
     *
     * @param string|array $condition Column name or key => value pairs
     * @param string|null $value Value to match exactly
     *
     * @return Repository
     */
    public function where(mixed $condition, mixed $value = null): Repository;

    /**
     * Filters results where condition contains value
     *
     * @param string|array $condition Column name or key => value pairs
     * @param string|null $value Value to contain
     *
     * @return Repository
     */
    public function contains(mixed $column, mixed $value = null): Repository;

    /**
     * Order results
     *
     * @param string $column
     * @param string $direction
     *
     * @return Repository
     */
    public function orderBy(string $column, string $direction = 'ASC'): Repository;
}
