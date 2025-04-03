<?php

namespace App\Services;

use App\Models\Order;

class OrderTypeCProcessor
{
    public function process(Order $order): void
    {
        $order->status = $order->flag ? 'completed' : 'in_progress';
    }
}
