<?php

namespace Tests\Unit;

use App\DTO\APIResponse;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

class APIClientContractTest extends TestCase
{
    public function testAPIClientContract()
    {
        // Mocked Order object
        $mockedOrder = new Order(1, 'B', 60, false);

        // Create an APIResponse with the mocked Order
        $response = new APIResponse('success', $mockedOrder);

        // Assert the response
        $this->assertInstanceOf(APIResponse::class, $response);
        $this->assertEquals('success', $response->status);
        $this->assertInstanceOf(Order::class, $response->data);
        $this->assertEquals(1, $response->data->id);
    }
}
