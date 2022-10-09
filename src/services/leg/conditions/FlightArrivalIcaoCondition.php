<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightArrivalIcaoCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        $result = in_array($flight->getRoute()->getArrivalICAO()->getValue(), (array)$value['array']);
        return (bool)$value['inverse'] ? !$result : $result;
    }
}