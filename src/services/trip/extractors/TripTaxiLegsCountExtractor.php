<?php declare(strict_types=1);

namespace ddd\pricing\services\trip\extractors;

use ddd\adapter\Trip\domain\aggregates\TripDecomposition;

final class TripTaxiLegsCountExtractor implements TripExtractorInterface
{
    public function extractValue(TripDecomposition $trip): int
    {
        return count($trip->getTaxiFlights());
    }
}