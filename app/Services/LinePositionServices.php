<?php

namespace App\Services;

use App\Services\DB;

class LinePositionServices
{
    private const MAX_POSITION = 3;
    private const SINGLE_CAR_POSITION = 4;
    private const X_THRESHOLD = 1;
    private const MAX_CARS = 9;

    public function getLinePosition($race_id, $car_no): ?int
    {
        if (!$this->isValidInput($race_id, $car_no)) {
            return null;
        }

        $forecast = $this->getRaceForecast($race_id);
        if (!$forecast) {
            return null;
        }

        return $this->calculateCarPosition($car_no, $forecast);
    }

    private function isValidInput($race_id, $car_no): bool
    {
        return is_numeric($race_id) && is_numeric($car_no);
    }

    private function getRaceForecast($race_id): ?object
    {
        return DB::table('forecast_for_race')
            ->where('race_id', $race_id)
            ->first();
    }

    private function calculateCarPosition(int $carNo, object $forecast): ?int
    {
        $cars = $this->extractCarPositions($forecast);
        if (empty($cars)) {
            return null;
        }

        $sortedCars = $this->sortCarsByCoordinates($cars);
        $groups = $this->groupCarsByXCoordinate($sortedCars);

        return $this->determinePositionInGroups($carNo, $groups);
    }

    private function extractCarPositions(object $forecast): array
    {
        $cars = [];
        for ($i = 1; $i <= self::MAX_CARS; $i++) {
            $xField = "car_no{$i}_x";
            $yField = "car_no{$i}_y";

            if (isset($forecast->$xField, $forecast->$yField)) {
                $cars[] = [
                    'car_no' => $i,
                    'x' => (int)$forecast->$xField,
                    'y' => (int)$forecast->$yField
                ];
            }
        }
        return $cars;
    }

    private function sortCarsByCoordinates(array $cars): array
    {
        usort($cars, fn($a, $b) =>
        $a['x'] === $b['x']
            ? $a['y'] - $b['y']
            : $a['x'] - $b['x']
        );
        return $cars;
    }

    private function groupCarsByXCoordinate(array $cars): array
    {
        $groups = [];
        $currentGroup = [];
        $previousX = null;

        foreach ($cars as $car) {
            if ($previousX !== null && abs($car['x'] - $previousX) > self::X_THRESHOLD) {
                $groups[] = $currentGroup;
                $currentGroup = [];
            }
            $currentGroup[] = $car;
            $previousX = $car['x'];
        }

        if (!empty($currentGroup)) {
            $groups[] = $currentGroup;
        }

        return $groups;
    }

    private function determinePositionInGroups(int $carNo, array $groups): ?int
    {
        foreach ($groups as $group) {
            usort($group, fn($a, $b) =>
            $a['x'] === $b['x']
                ? $a['y'] - $b['y']
                : $a['x'] - $b['x']
            );

            foreach ($group as $car) {
                if ($car['car_no'] === $carNo) {
                    return $this->calculatePositionInGroup($group, $car);
                }
            }
        }
        return null;
    }

    private function calculatePositionInGroup(array $group, array $car): int
    {
        if (count($group) === 1) {
            return self::SINGLE_CAR_POSITION;
        }

        $xValues = array_column($group, 'x');
        sort($xValues);
        $position = array_search($car['x'], $xValues) + 1;

        return min($position, self::MAX_POSITION);
    }
}