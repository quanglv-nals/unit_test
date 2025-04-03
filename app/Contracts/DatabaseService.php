<?php

namespace App\Contracts;

use App\Exceptions\DatabaseException;

interface DatabaseService
{
    /**
     * Get orders by user ID.
     *
     * @param int $userId
     * @return array
     * @throws DatabaseException If there is an issue retrieving orders.
     */
    public function getOrdersByUser(int $userId): array;

    /**
     * Update the status of an order in the database.
     *
     * @param int $orderId
     * @param string $status
     * @param string $priority
     * @return bool
     * @throws DatabaseException If there is an issue updating the order.
     */
    public function updateOrderStatus(int $orderId, string $status, string $priority): bool;
}
