<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;
use ddd\pricing\interfaces\AircraftPricingHelperInterface;

final class FlightDepartureCityCondition implements FlightConditionInterface
{
    private AircraftPricingHelperInterface $helper;

    public function __construct(AircraftPricingHelperInterface $helper)
    {
        $this->helper = $helper;
    }

    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $result = in_array($this->helper->getCityIdByICAO($flight->getRoute()->getDepartureICAO()), (array)$value['array']);
        return (bool)$value['inverse'] ? !$result : $result;
    }
}