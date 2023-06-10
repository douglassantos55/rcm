<?php

namespace App\Repositories;

interface RentRepository extends Repository
{
    /**
     * Saves rent items
     *
     * @param mixed $rent
     * @param array $items
     *
     * @return mixed The created items
     */
    public function createItems(mixed $rent, array $items): mixed;
};
