<?php
namespace App\Services;

use App\Contracts\APIClient;
use App\DTO\APIResponse;
use App\Models\Order;

class APIClientService implements APIClient
{
    public function callAPI(int $orderId): APIResponse
    {
        // Mocked API response for demonstration
        $mockedOrder = new Order($orderId, 'B', 60, false); // Replace with actual API logic
        return new APIResponse('success', $mockedOrder);
    }
}
