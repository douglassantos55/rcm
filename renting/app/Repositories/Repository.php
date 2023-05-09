<?php

namespace App\Repositories;

interface Repository
{
    /**
     * Get results
     *
     * @return array
     */
    public function get(): array;

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
