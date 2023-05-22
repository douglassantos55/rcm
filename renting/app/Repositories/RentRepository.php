<?php

namespace App\Repositories;

interface RentRepository extends Repository
{
    /**
     * Saves rent items
     *
     * @param string $rentId
     * @param array $items
     *
     * @return mixed The created items
     */
    public function createItems(string $rentId, array $items): mixed;
};
