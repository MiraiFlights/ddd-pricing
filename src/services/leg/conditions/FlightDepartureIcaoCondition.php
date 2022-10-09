<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightDepartureIcaoCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $result = in_array($flight->getRoute()->getDepartureICAO()->getValue(), (array)$value['array']);
        return (bool)$value['inverse'] ? !$result : $result;
    }
}