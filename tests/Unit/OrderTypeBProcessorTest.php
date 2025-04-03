<?php

namespace Tests\Unit;

use App\Services\OrderTypeBProcessor;
use App\Models\Order;
use App\DTO\APIResponse;
use Mockery;

test('processes Type B orders successfully', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 80, false);
    $apiResponse = new APIResponse('success', new Order(999, 'B', 60, false));

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn($apiResponse);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_PROCESSED);
});

test('processes Type B orders successfully with high API data', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 80, false);
    $apiResponse = new APIResponse('success', new Order(999, 'B', 60, false));

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn($apiResponse);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_PROCESSED);
});

test('sets status to pending when API data is low', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 120, false);
    $apiResponse = new APIResponse('success', new Order(999, 'B', 40, false));

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn($apiResponse);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_PENDING);
});

test('sets status to pending when flag is true', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 120, true);
    $apiResponse = new APIResponse('success', new Order(999, 'B', 60, false));

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn($apiResponse);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_PENDING);
});

test('sets status to error when none of the conditions are met', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 120, false);
    $apiResponse = new APIResponse('success', new Order(999, 'B', 60, false));

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn($apiResponse);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_ERROR);
});

test('sets status to api_error when API response status is not success', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 80, false);
    $apiResponse = new APIResponse('failure', new Order(999, 'B', 60, false));

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andReturn($apiResponse);

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_API_ERROR);
});

test('sets status to api_failure on API exception', function () {
    $apiClientMock = Mockery::mock(\App\Contracts\APIClient::class);
    $processor = new OrderTypeBProcessor($apiClientMock);

    $order = new Order(2, 'B', 80, false);

    $apiClientMock->shouldReceive('callAPI')
        ->once()
        ->with($order->id)
        ->andThrow(new \App\Exceptions\APIException('API Error'));

    $processor->process($order);

    expect($order->status)->toBe(Order::STATUS_API_FAILURE);
});
