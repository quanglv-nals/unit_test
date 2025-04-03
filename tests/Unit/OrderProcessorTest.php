<?php

namespace Tests\Unit;

use App\Services\OrderProcessor;
use App\Services\OrderTypeAProcessor;
use App\Services\OrderTypeBProcessor;
use App\Services\OrderTypeCProcessor;
use App\Models\Order;
use Mockery;

beforeEach(function () {
    $this->typeAProcessorMock = Mockery::mock(OrderTypeAProcessor::class);
    $this->typeBProcessorMock = Mockery::mock(OrderTypeBProcessor::class);
    $this->typeCProcessorMock = Mockery::mock(OrderTypeCProcessor::class);

    $this->orderProcessor = new OrderProcessor(
        $this->typeAProcessorMock,
        $this->typeBProcessorMock,
        $this->typeCProcessorMock
    );
});

afterEach(function () {
    Mockery::close();
});

test('processes Type A orders', function () {
    $order = new Order(1, 'A', 250, true);

    $this->typeAProcessorMock->shouldReceive('process')
        ->once()
        ->with($order, 1, Mockery::any())
        ->andReturnUsing(function (Order $order) {
            $order->status = 'exported'; // Simulate status set by TypeAProcessor
        });

    $this->orderProcessor->process($order, 1, fn($filename, $mode) => fopen('php://memory', $mode));

    expect($order->status)->toBe('exported');
});

test('processes Type B orders', function () {
    $order = new Order(2, 'B', 80, false);

    $this->typeBProcessorMock->shouldReceive('process')
        ->once()
        ->with($order)
        ->andReturnUsing(function (Order $order) {
            $order->status = 'processed'; // Simulate status set by TypeBProcessor
        });

    $this->orderProcessor->process($order, 1, fn($filename, $mode) => fopen('php://memory', $mode));

    expect($order->status)->toBe('processed');
});

test('processes Type C orders', function () {
    $order = new Order(3, 'C', 120, true);

    $this->typeCProcessorMock->shouldReceive('process')
        ->once()
        ->with($order)
        ->andReturnUsing(function (Order $order) {
            $order->status = 'completed'; // Simulate status set by TypeCProcessor
        });

    $this->orderProcessor->process($order, 1, fn($filename, $mode) => fopen('php://memory', $mode));

    expect($order->status)->toBe('completed');
});

test('sets status to unknown_type for unsupported order types', function () {
    $order = new Order(4, 'X', 100, false);

    $this->orderProcessor->process($order, 1, fn($filename, $mode) => fopen('php://memory', $mode));

    expect($order->status)->toBe('unknown_type');
});
