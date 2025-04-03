<?php
namespace App\Services;

use App\Contracts\DatabaseServiceInterface;

class DatabaseService implements DatabaseServiceInterface
{
    public function getOrdersByUser(int $userId): array
    {
        // Simulate a list of orders
        return [
            (object) ['id' => 1, 'type' => 'A', 'amount' => 250, 'flag' => true, 'status' => 'new'],
            (object) ['id' => 2, 'type' => 'B', 'amount' => 80, 'flag' => false, 'status' => 'new'],
            (object) ['id' => 3, 'type' => 'C', 'amount' => 120, 'flag' => true, 'status' => 'new'],
        ];
    }

    public function updateOrderStatus(int $orderId, string $status, string $priority): void
    {
        // Simulate updating the order status
        echo "Order {$orderId} updated with status '{$status}' and priority '{$priority}'.\n";
    }
}
