<?php

namespace App\DTO;

use App\Models\Order;

/**
 * Data Transfer Object for API responses.
 */
class APIResponse
{
    /**
     * @var string The status of the API response.
     */
    public string $status;

    /**
     * @var Order The data returned by the API.
     */
    public Order $data;

    /**
     * Constructor.
     *
     * @param string $status The status of the API response.
     * @param Order $data The data returned by the API.
     */
    public function __construct(string $status, Order $data)
    {
        $this->status = $status;
        $this->data = $data;
    }
}
