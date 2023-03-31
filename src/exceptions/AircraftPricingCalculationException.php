<?php declare(strict_types=1);

namespace ddd\pricing\exceptions;

use ddd\domain\exceptions\DomainServiceExceptionInterface;

class AircraftPricingCalculationException extends \RuntimeException implements DomainServiceExceptionInterface
{
    public $message = 'AIRCRAFT_PRICING_PROFILE_CALCULATION_ERROR';
}