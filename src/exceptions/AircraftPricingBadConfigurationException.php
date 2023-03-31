<?php declare(strict_types=1);

namespace ddd\pricing\exceptions;

use ddd\pricing\interfaces\AircraftPricingProfileExceptionInterface;

final class AircraftPricingBadConfigurationException extends AircraftPricingCalculationException implements AircraftPricingProfileExceptionInterface
{
    public $message = 'AIRCRAFT_PRICING_BAD_CONFIGURATION';
}