<?php

namespace Tests\Unit;

use App\Services\OrderTypeCProcessor;
use App\Models\Order;

test('processes Type C orders with flag enabled', function () {
    $processor = new OrderTypeCProcessor();
    $order = new Order(3, 'C', 120, true);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_COMPLETED);
});

test('processes Type C orders with flag disabled', function () {
    $processor = new OrderTypeCProcessor();
    $order = new Order(3, 'C', 120, false);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_IN_PROGRESS);
});

test('processes Type C orders with zero amount', function () {
    $processor = new OrderTypeCProcessor();
    $order = new Order(4, 'C', 0, true);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_COMPLETED);
});
