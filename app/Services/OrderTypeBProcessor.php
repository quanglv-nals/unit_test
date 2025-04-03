<?php

namespace App\Services;

use App\Contracts\APIClient;
use App\Models\Order;
use App\Exceptions\APIException;

class OrderTypeBProcessor
{
    private APIClient $apiClient;

    public function __construct(APIClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function process(Order $order): void
    {
        try {
            $apiResponse = $this->apiClient->callAPI($order->id);

            if ($apiResponse->status === 'success') {
                if ($apiResponse->data->amount >= 50 && $order->amount < 100) {
                    $order->status = 'processed';
                } elseif ($apiResponse->data->amount < 50 || $order->flag) {
                    $order->status = 'pending';
                } else {
                    $order->status = 'error';
                }
            } else {
                $order->status = 'api_error';
            }
        } catch (APIException $e) {
            $order->status = 'api_failure';
        }
    }
}
