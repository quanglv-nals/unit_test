<?php

namespace Tests\Unit;

use App\DTO\APIResponse;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

class APIResponseTest extends TestCase
{
    public function testAPIResponseInitialization()
    {
        // Create a mocked Order object
        $mockedOrder = new Order(1, 'B', 60, false);

        // Initialize APIResponse with the mocked Order
        $response = new APIResponse('success', $mockedOrder);

        // Assert the response properties
        $this->assertEquals('success', $response->status);
        $this->assertInstanceOf(Order::class, $response->data);
        $this->assertEquals(1, $response->data->id);
        $this->assertEquals('B', $response->data->type);
        $this->assertEquals(60, $response->data->amount);
        $this->assertFalse($response->data->flag);
    }

    public function testAPIResponseWithDifferentStatus()
    {
        // Create a mocked Order object
        $mockedOrder = new Order(2, 'A', 150, true);

        // Initialize APIResponse with a different status
        $response = new APIResponse('error', $mockedOrder);

        // Assert the response properties
        $this->assertEquals('error', $response->status);
        $this->assertInstanceOf(Order::class, $response->data);
        $this->assertEquals(2, $response->data->id);
        $this->assertEquals('A', $response->data->type);
        $this->assertEquals(150, $response->data->amount);
        $this->assertTrue($response->data->flag);
    }

    public function testAPIResponseHandlesEmptyOrder()
    {
        // Create a mocked Order object with minimal data
        $mockedOrder = new Order(0, '', 0, false);

        // Initialize APIResponse with the mocked Order
        $response = new APIResponse('empty', $mockedOrder);

        // Assert the response properties
        $this->assertEquals('empty', $response->status);
        $this->assertInstanceOf(Order::class, $response->data);
        $this->assertEquals(0, $response->data->id);
        $this->assertEquals('', $response->data->type);
        $this->assertEquals(0, $response->data->amount);
        $this->assertFalse($response->data->flag);
    }
}

