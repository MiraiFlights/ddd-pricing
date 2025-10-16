<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightAircraftTypeCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $result = in_array($flight->getAircraft()->getProperties()->getAircraftTypeId()?->getValue(), (array)$value['array']);
        return (bool)$value['inverse'] ? !$result : $result;
    }
}