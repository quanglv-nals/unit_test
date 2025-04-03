<?php

namespace Tests\Unit;

use App\Services\DatabaseService;
use PHPUnit\Framework\TestCase;

class DatabaseServiceTest extends TestCase
{
    public function testGetOrdersByUser()
    {
        $dbService = new DatabaseService();
        $orders = $dbService->getOrdersByUser(1);

        $this->assertIsArray($orders);
        $this->assertCount(3, $orders);
        $this->assertEquals('A', $orders[0]->type);
    }

    public function testUpdateOrderStatus()
    {
        $dbService = new DatabaseService();

        $this->expectOutputString("Order 1 updated with status 'processed' and priority 'high'.\n");
        $dbService->updateOrderStatus(1, 'processed', 'high');
    }
}

