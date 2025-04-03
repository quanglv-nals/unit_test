<?php
namespace App\Contracts;

use App\Models\Order;

interface DatabaseServiceInterface
{
    /**
     * Fetch orders for a given user ID.
     *
     * @param int $userId
     * @return Order[]
     */
    public function getOrdersByUser(int $userId): array;

    /**
     * Update the status and priority of an order.
     *
     * @param int $orderId
     * @param string $status
     * @param string $priority
     * @return void
     */
    public function updateOrderStatus(int $orderId, string $status, string $priority): void;
}
