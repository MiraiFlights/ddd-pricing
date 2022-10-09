<?php declare(strict_types=1);

namespace ddd\pricing\services\trip\extractors;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\adapter\Trip\domain\aggregates\TripDecomposition;

final class TripTripDaysCountExtractor implements TripExtractorInterface
{
    public function extractValue(TripDecomposition $trip): int
    {
        return (int)floor(array_reduce(
                $trip->getFlights(),
                fn(float $carry, FlightDecomposition $flight) => $carry
                    + $flight->getRoute()->getTotalTime()->inHours()
                    + $flight->getParkingInterval()->inHours(),
                0.0
            ) / 24);
    }
}