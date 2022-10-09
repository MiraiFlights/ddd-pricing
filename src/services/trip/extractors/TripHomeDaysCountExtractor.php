<?php declare(strict_types=1);

namespace ddd\pricing\services\trip\extractors;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\adapter\Trip\domain\aggregates\TripDecomposition;

final class TripHomeDaysCountExtractor implements TripExtractorInterface
{
    public function extractValue(TripDecomposition $trip): int
    {
        return (int)array_reduce(
            $trip->getFlights(),
            function (float $carry, FlightDecomposition $flight) {
                $aircraftHomeICAO = $flight->getAircraft()->getProperties()->getHomeICAO();
                if ($aircraftHomeICAO && $aircraftHomeICAO->isEqualTo($flight->getRoute()->getArrivalICAO()))
                    $carry += floor($flight->getParkingInterval()->inHours() / 24);

                return $carry;
            },
            0.0
        );
    }
}