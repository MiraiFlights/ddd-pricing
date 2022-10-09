<?php declare(strict_types=1);

namespace ddd\pricing\services\leg\conditions;

use ddd\adapter\Trip\domain\aggregates\FlightDecomposition;

final class FlightFerryCondition implements FlightConditionInterface
{
    public function isSatisfiedBy($value, FlightDecomposition $flight): bool
    {
        if ($flight->isEmpty() && !(int)$value)
            return false;
        if (!$flight->isEmpty() && (int)$value)
            return false;

        return true;
    }
}