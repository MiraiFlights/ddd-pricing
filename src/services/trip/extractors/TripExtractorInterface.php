<?php declare(strict_types=1);

namespace ddd\pricing\services\trip\extractors;

use ddd\adapter\Trip\domain\aggregates\TripDecomposition;

interface TripExtractorInterface
{
    public function extractValue(TripDecomposition $trip): int;
}