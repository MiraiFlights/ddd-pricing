<?php declare(strict_types=1);

namespace ddd\pricing\services\trip\extractors;

use ddd\adapter\Trip\domain\aggregates\TripDecomposition;

final class TripFlightTtlExtractor implements TripExtractorInterface
{
    public function extractValue(TripDecomposition $trip): int
    {
        $flights = $trip->getTaxiFlights();
        // #FT-4900
        if (count($flights) == 0)
            return 9999;
        $firstFlight = reset($flights);
        $timeLeft = $firstFlight->getDepartureDate()->getTimestamp() - time();
        return $timeLeft <= 0 ? 0 : intval($timeLeft / 3600);
    }
}