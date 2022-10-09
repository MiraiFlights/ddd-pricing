<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\extractors;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightNightStopsCountExtractor implements FlightExtractorInterface
{
    public function extractValue(FlightDecomposition $flight): float
    {
        $hours = $flight->getParkingInterval()->inHours();

        if ($hours < 4) {
            return 0;
        } else {
            return ceil($hours / 24);
        }
    }
}