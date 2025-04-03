<?php

namespace Tests\Unit;

use App\Contracts\DatabaseServiceInterface;
use App\Services\OrderProcessingService;
use App\Contracts\APIClient;
use App\Models\Order;
use App\DTO\APIResponse;
use Mockery;
use App\Exceptions\DatabaseException;
use App\Exceptions\APIException;

beforeEach(function () {
    $this->dataServiceMock = Mockery::mock(DatabaseServiceInterface::class);
    $this->apiClientMock = Mockery::mock(APIClient::class);
    $this->orderProcessingService = new OrderProcessingService($this->dataServiceMock, $this->apiClientMock);
});

afterEach(function () {
    Mockery::close();
});

test('processes orders successfully', function () {
    $userId = 1;
    $orders = [
        new Order(1, 'A', 250, true),
        new Order(2, 'B', 80, false),
    ];

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with($userId)
        ->andReturn($orders);

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->twice();

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with(2)
        ->andReturn(new APIResponse('success', new Order(2, 'B', 60, false)));

    $result = $this->orderProcessingService->processOrders($userId);

    expect($result)->toHaveCount(2);
    expect($result[0]->status)->toBe(Order::STATUS_EXPORTED);
    expect($result[1]->status)->toBe(Order::STATUS_PROCESSED);
});

test('processes Type A orders with high value', function () {
    $order = new Order(1, 'A', 250, true);
    $userId = 1;

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with($userId)
        ->andReturn([$order]);

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_EXPORTED, 'high');

    $this->orderProcessingService->processOrders($userId);

    expect($order->status)->toBe(Order::STATUS_EXPORTED);
});

test('handles API error for Type B orders', function () {
    $order = new Order(2, 'B', 80, false);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andThrow(new APIException('API Error'));

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_API_FAILURE, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_API_FAILURE);
});

test('processes Type C orders with flag enabled', function () {
    $order = new Order(3, 'C', 120, true);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_COMPLETED, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_COMPLETED);
});

test('handles database exception gracefully', function () {
    $order = new Order(1, 'A', 250, true);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->andThrow(new DatabaseException('Database Error'));

    $result = $this->orderProcessingService->processOrders(1);

    expect($result)->toBeFalse();
    expect($order->status)->toBe(Order::STATUS_DB_ERROR);
});

test('handles unknown order type', function () {
    $order = new Order(1, 'X', 100, false);
    $userId = 1;

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with($userId)
        ->andReturn([$order]);

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_UNKNOWN_TYPE, 'low');

    $this->orderProcessingService->processOrders($userId);

    expect($order->status)->toBe(Order::STATUS_UNKNOWN_TYPE);
});

test('handles database update failure', function () {
    $order = new Order(1, 'A', 250, true);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->andThrow(new DatabaseException('Database Error'));

    $result = $this->orderProcessingService->processOrders(1);

    expect($result)->toBeFalse();
    expect($order->status)->toBe(Order::STATUS_DB_ERROR);
});

test('handles export failure for Type A orders', function () {
    $order = new Order(1, 'A', 250, true);
    $userId = 1;

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with($userId)
        ->andReturn([$order]);

    $orderProcessingServiceMock = Mockery::mock(OrderProcessingService::class, [$this->dataServiceMock, $this->apiClientMock])
        ->makePartial();

    $orderProcessingServiceMock->shouldReceive('processTypeAOrder')
        ->once()
        ->with($order, $userId, Mockery::any())
        ->andReturnUsing(function () use ($order) {
            $order->status = Order::STATUS_EXPORT_FAILED;
        });

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->never();

    $result = $orderProcessingServiceMock->processOrders($userId);

    expect($result)->toBeFalse();
    expect($order->status)->toBe(Order::STATUS_EXPORT_FAILED);
});

test('handles API failure gracefully', function () {
    $order = new Order(2, 'B', 80, false);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andThrow(new APIException('API Failure'));

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_API_FAILURE, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_API_FAILURE);
});

test('handles empty orders', function () {
    $userId = 1;

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with($userId)
        ->andReturn([]);

    $result = $this->orderProcessingService->processOrders($userId);

    expect($result)->toBe([]);
});

test('sets status to processed when apiData->amount >= 50 and amount < 100', function () {
    $order = new Order(1, 'B', 80, false);
    $apiData = new Order(999, 'B', 60, false);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn(new APIResponse('success', $apiData));

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_PROCESSED, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_PROCESSED);
});

test('sets status to pending when apiData->amount < 50', function () {
    $order = new Order(1, 'B', 120, false);
    $apiData = new Order(999, 'B', 40, false);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn(new APIResponse('success', $apiData));

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_PENDING, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_PENDING);
});

test('sets status to pending when flag is true', function () {
    $order = new Order(1, 'B', 120, true);
    $apiData = new Order(999, 'B', 60, false);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn(new APIResponse('success', $apiData));

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_PENDING, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_PENDING);
});

test('sets status to error when none of the conditions are met', function () {
    $order = new Order(1, 'B', 120, false);
    $apiData = new Order(999, 'B', 60, false);

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->andReturn([$order]);

    $this->apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn(new APIResponse('success', $apiData));

    $this->dataServiceMock->shouldReceive('updateOrderStatus')
        ->once()
        ->with($order->id, Order::STATUS_ERROR, 'low');

    $this->orderProcessingService->processOrders(1);

    expect($order->status)->toBe(Order::STATUS_ERROR);
});

test('handles exception during order processing', function () {
    $userId = 1;

    $this->dataServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with($userId)
        ->andThrow(new \Exception('Database Error'));

    $result = $this->orderProcessingService->processOrders($userId);

    expect($result)->toBeFalse();
});

