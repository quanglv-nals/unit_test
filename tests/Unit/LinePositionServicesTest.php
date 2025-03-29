<?php

use App\Services\LinePositionServices;

beforeEach(function () {
    // Mock the DB service
    $this->mock = Mockery::mock('alias:App\Services\DB');
    $this->mock->shouldReceive('table')->with('forecast_for_race')->andReturnSelf();
    $this->service = new LinePositionServices();
});

afterEach(function () {
    Mockery::close();
});

test('returns null when race_id or car_no is invalid', function () {
    expect($this->service->getLinePosition('invalid', 1))->toBeNull();
    expect($this->service->getLinePosition(1, 'invalid'))->toBeNull();
    expect($this->service->getLinePosition('invalid', 'invalid'))->toBeNull();
});

test('returns null when no forecast is found', function () {
    $this->mock
        ->shouldReceive('where')->with('race_id', 1)->andReturnSelf()
        ->shouldReceive('first')->andReturn(null);

    expect($this->service->getLinePosition(1, 1))->toBeNull();
});

test('returns position 4 for a single car in the group', function () {
    mockDBResponse([
        'car_no1_x' => 10, 'car_no1_y' => 20,
    ]);

    expect($this->service->getLinePosition(1, 1))->toBe(4);
});

test('returns null for a car that does not exist', function () {
    mockDBResponse([
        'car_no1_x' => 10, 'car_no1_y' => 20,
        'car_no2_x' => 15, 'car_no2_y' => 25,
    ]);

    expect($this->service->getLinePosition(1, 3))->toBeNull();
});

/**
 * Helper function to mock DB responses
 *
 * @param array $forecastData Mocked forecast data
 */
function mockDBResponse(array $forecastData)
{
    $forecast = (object) $forecastData;

    test()->mock
        ->shouldReceive('where')->with('race_id', 1)->andReturnSelf()
        ->shouldReceive('first')->andReturn($forecast);
}


