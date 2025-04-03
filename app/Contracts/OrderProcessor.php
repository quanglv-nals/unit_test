<?php
namespace App\Contracts;

use App\Models\Order;

interface OrderProcessor
{
    public function process(Order $order): void;
}
