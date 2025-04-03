<?php

use App\Models\Order;
use App\Services\DatabaseService;

afterEach(function () {
    Mockery::close();
});


test('mocked database service', function () {
    $dbServiceMock = Mockery::mock(DatabaseService::class);
    $dbServiceMock->shouldReceive('getOrdersByUser')
        ->once()
        ->with(1)
        ->andReturn([
            new Order(1, 'A', 250, true),
            new Order(2, 'B', 80, false),
        ]);

    $orders = $dbServiceMock->getOrdersByUser(1);

    expect($orders)->toBeArray();
    expect($orders)->toHaveCount(2);
    expect($orders[0]->type)->toBe('A');
    expect($orders[0]->amount)->toBe(250);
});
