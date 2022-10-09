<?php declare(strict_types=1);

namespace ddd\pricing\interfaces;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\adapter\Trip\domain\aggregates\TripDecomposition;
use ddd\aviation\values\ICAO;
use ddd\pricing\entities\AircraftPricingCalculator;

interface AircraftPricingHelperInterface
{
    public function getCityIdByICAO(ICAO $icao): string;

    public function getCountryISO3ByICAO(ICAO $icao): string;

    public function isInnerRoute(FlightDecomposition $flight): bool;

    /**
     * @param TripDecomposition $trip
     * @return AircraftPricingCalculator[]|\Generator
     */
    public function getActualCalculators(TripDecomposition $trip): \Generator;
}