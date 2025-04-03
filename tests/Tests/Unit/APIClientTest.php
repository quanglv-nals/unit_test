<?php

namespace Tests\Unit;

use App\Services\APIClient;
use App\DTO\APIResponse;
use PHPUnit\Framework\TestCase;

class APIClientTest extends TestCase
{
    public function testCallAPI()
    {
        $apiClient = new APIClient();

        // Call the method
        $response = $apiClient->callAPI(1);

        // Assertions
        $this->assertInstanceOf(APIResponse::class, $response);
        $this->assertEquals('success', $response->status);
        $this->assertGreaterThanOrEqual(30, $response->data);
        $this->assertLessThanOrEqual(70, $response->data);
    }
}
