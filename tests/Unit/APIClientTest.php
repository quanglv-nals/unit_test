<?php

namespace Tests\Unit;

use App\Services\APIClientService;
use App\DTO\APIResponse;
use App\Models\Order;
use PHPUnit\Framework\TestCase;

class APIClientTest extends TestCase
{
    public function testCallAPI()
    {
        $apiClient = new APIClientService();

        // Call the method
        $response = $apiClient->callAPI(1);

        // Assert the response is an instance of APIResponse
        $this->assertInstanceOf(APIResponse::class, $response);

        // Assert the status is 'success'
        $this->assertEquals('success', $response->status);

        // Assert the data is an instance of Order
        $this->assertInstanceOf(Order::class, $response->data);

        // Assert the Order object has the correct properties
        $this->assertEquals(1, $response->data->id);
        $this->assertEquals('B', $response->data->type);
        $this->assertEquals(60, $response->data->amount);
        $this->assertFalse($response->data->flag);
    }
}