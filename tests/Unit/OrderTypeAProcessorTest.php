<?php

namespace Tests\Unit;

use App\Services\OrderTypeAProcessor;
use App\Models\Order;

test('processes Type A orders successfully', function () {
    $processor = new OrderTypeAProcessor();
    $order = new Order(1, 'A', 250, true);

    $fileOpener = fn($filename, $mode) => fopen('php://memory', $mode);

    $processor->process($order, 1, $fileOpener);

    expect($order->status)->toBe(Order::STATUS_EXPORTED);
});

test('sets status to export_failed when file cannot be opened', function () {
    $processor = new OrderTypeAProcessor();
    $order = new Order(1, 'A', 250, true);

    $fileOpener = fn($filename, $mode) => false; // Simulate file opening failure

    $processor->process($order, 1, $fileOpener);

    expect($order->status)->toBe(Order::STATUS_EXPORT_FAILED);
});

test('processes Type A orders successfully with high value', function () {
    $processor = new OrderTypeAProcessor();
    $order = new Order(1, 'A', 250, true);

    $fileOpener = fn($filename, $mode) => fopen('php://memory', $mode);

    $processor->process($order, 1, $fileOpener);

    expect($order->status)->toBe(Order::STATUS_EXPORTED);
});

test('processes Type A orders with low value', function () {
    $processor = new OrderTypeAProcessor();
    $order = new Order(2, 'A', 100, false);

    $fileOpener = fn($filename, $mode) => fopen('php://memory', $mode);

    $processor->process($order, 1, $fileOpener);

    expect($order->status)->toBe(Order::STATUS_EXPORTED);
});
